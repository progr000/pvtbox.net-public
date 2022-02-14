<?php

use yii\db\Migration;

/**
 * Class m180119_173801_change_copy_functions
 */
class m180119_173801_change_copy_functions extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $schema   = isset(Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema'])
            ? Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema']
            : 'public';

        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        $userName = isset(Yii::$app->components['db']['username'])
            ? Yii::$app->components['db']['username']
            : 'username';

        /* ALTER copy_collaborated */
        Yii::$app->db->pdo->exec("
            SET search_path TO {$schema};

            CREATE OR REPLACE FUNCTION copy_collaborated(
                id bigint,
                parent_id bigint,
                separator character varying)
                RETURNS SETOF record_copy
                LANGUAGE 'plpgsql'

            AS \$BODY\$

            declare
                _event {$tablePrefix}user_file_events%rowtype;
                _parent {$tablePrefix}user_files%rowtype;
                _file record;
                _rec record_copy;
                _t boolean;
            begin
                select * from {$tablePrefix}user_files where file_id = parent_id into _parent;

                /* check that destination folder exists */
                if _parent is null then
                    raise EXCEPTION 'folder with file_id=% does not exist', parent_id;
                end if;

                /* check that destination folder is folder (no regular file) */
                if _parent.is_folder <> 1 then
                    raise EXCEPTION 'file with file_id=% is not folder', parent_id;
                end if;

                -- The same user_id for source and parent folder not allowed
                select s.user_id = d.user_id
                    from {$tablePrefix}user_files s, {$tablePrefix}user_files d
                    where s.file_id = id  -- source
                    and d.file_id = parent_id  -- destination
                    into _t;
                if _t = true
                then
                    raise EXCEPTION 'The same user_id for source and parent folder.'
                                ' Operation not allowed.';
                end if;

                for _file in select * from (
                    with recursive obj_tree as (
                        select f1.*,
                            nextval('{$tablePrefix}user_files_file_id_seq') as new_id,
                            concat_ws(separator,
                                get_full_path(_parent.file_id, separator),
                                file_name) as file_path
                            from {$tablePrefix}user_files f1
                            where f1.file_id = id  -- input parameter
                            and f1.is_deleted = 0
                        union all
                        select f2.*,
                            nextval('{$tablePrefix}user_files_file_id_seq') as new_id,
                            concat_ws(separator, tt.file_path, f2.file_name) as file_path
                            from {$tablePrefix}user_files f2
                            join obj_tree tt on tt.file_id = f2.file_parent_id
                            where f2.is_deleted = 0

                    )
                    select t1.*,
                        coalesce(t2.new_id, parent_id) as new_parent_id, -- input parameter
                        coalesce(t2.file_uuid, _parent.file_uuid) as parent_uuid
                    from obj_tree t1
                    left join obj_tree t2 on t1.file_parent_id = t2.file_id
                    order by t1.new_id
                ) as files
                loop
                    _rec = null;

                    -- get last event
                    select * from {$tablePrefix}user_file_events
                        where file_id = _file.file_id  -- use old file_id
                        and event_type <> 2  -- TYPE_DELETE
                        order by event_id desc limit 1
                        into _event;

                    if _event is null then
                        raise EXCEPTION 'last_event for file_id=% not found', _rec.file_id;
                    end if;

                    -- populate record, files part
                    _rec.file_id  = _file.new_id;
                    _rec.file_uuid = _file.file_uuid;  -- from source !!
                    _rec.file_parent_id = _file.new_parent_id;
                    _rec.file_name = _file.file_name;
                    _rec.file_size = _file.file_size;
                    _rec.folder_children_count = _file.folder_children_count;
                    _rec.file_md5 = _file.file_md5;
                    _rec.file_created = extract(epoch from now())::bigint;
                    _rec.file_updated = _rec.file_created;
                    _rec.file_lastatime = _rec.file_created;
                    _rec.is_folder = _file.is_folder;
                    _rec.is_deleted = _file.is_deleted;
                    _rec.is_updated = _file.is_updated;
                    _rec.is_outdated = _file.is_outdated;
                    _rec.last_event_type = _file.last_event_type;
                    _rec.diff_file_uuid = _file.diff_file_uuid;
                    _rec.user_id = _parent.user_id;  -- from parent !!
                    _rec.node_id = _parent.node_id;  -- from parent !!
                    _rec.collaboration_id = _parent.collaboration_id; -- from parent
                    _rec.is_collaborated = _file.is_collaborated;
                    _rec.is_owner = _parent.is_owner; -- from parent
                    _rec.is_shared = 0;
                    if _parent.share_group_hash is not null then
                        _rec.share_hash = md5(concat(_rec.file_uuid, _rec.user_id, _rec.is_deleted));
                    end if;  -- else null
                    _rec.share_group_hash = _parent.share_group_hash;  -- from parent
                    _rec.share_created = _parent.share_created;  -- from parent
                    _rec.share_lifetime = _parent.share_lifetime;  -- from parent
                    _rec.share_ttl_info = _parent.share_ttl_info;  -- from parent
                    _rec.share_password = _parent.share_password;  -- from parent
                    _rec.file_path = _file.file_path;
                    _rec.parent_folder_uuid = _file.parent_uuid;

                    -- populate record, events part
                    _rec.event_id = nextval('{$tablePrefix}user_file_events_event_id_seq');
                    -- _rec.event_uuid = md5(text(uuid_generate_v4()));  -- not from source
                    _rec.event_uuid = _event.event_uuid;  -- from source
                    _rec.event_type = _event.event_type;
                    _rec.event_timestamp = _rec.file_created; --?? _last_event.event_timestamp;
                    _rec.event_invisible = _event.event_invisible;
                    _rec.last_event_id = 0;
                    _rec.diff_file_uuid = _event.diff_file_uuid;
                    _rec.diff_file_size = _event.diff_file_size;
                    _rec.rev_diff_file_uuid = _event.rev_diff_file_uuid;
                    _rec.rev_diff_file_size = _event.rev_diff_file_size;
                    _rec.file_hash_before_event = _event.file_hash_before_event;
                    _rec.file_hash = _event.file_hash;
                    _rec.file_name_before_event = _event.file_name_before_event;
                    _rec.file_name_after_event = _event.file_name_after_event;
                    _rec.file_size_before_event = _event.file_size_before_event;
                    _rec.file_size_after_event = _event.file_size_after_event;
                    _rec.erase_nested = 0;

                    -- insert file
                    insert into {$tablePrefix}user_files
                       (file_id, file_parent_id, file_uuid, file_name,
                        file_size, folder_children_count, file_md5, file_created, file_updated,
                        file_lastatime, is_folder, is_deleted, is_updated,
                        is_outdated, last_event_type, diff_file_uuid,
                        user_id, node_id, collaboration_id, is_collaborated,
                        is_owner, is_shared, share_hash, share_group_hash,
                        share_created, share_lifetime, share_ttl_info,
                        share_password)
                    values (_rec.file_id, _rec.file_parent_id, _rec.file_uuid, _rec.file_name,
                        _rec.file_size, _rec.folder_children_count, _rec.file_md5, _rec.file_created, _rec.file_updated,
                        _rec.file_lastatime, _rec.is_folder, _rec.is_deleted, _rec.is_updated,
                        _rec.is_outdated, _rec.last_event_type, _rec.diff_file_uuid,
                        _rec.user_id, _rec.node_id, _rec.collaboration_id, _rec.is_collaborated,
                        _rec.is_owner, _rec.is_shared, _rec.share_hash, _rec.share_group_hash,
                        _rec.share_created, _rec.share_lifetime, _rec.share_ttl_info,
                        _rec.share_password);

                    --insert event
                    insert into {$tablePrefix}user_file_events
                       (file_id, event_id, event_uuid, event_type,
                        event_timestamp, event_invisible, last_event_id,
                        diff_file_uuid, diff_file_size, rev_diff_file_uuid,
                        rev_diff_file_size, file_hash_before_event, file_hash,
                        file_name_before_event, file_name_after_event,
                        file_size_before_event, file_size_after_event,
                        erase_nested, node_id, user_id)
                    values
                       (_rec.file_id, _rec.event_id, _rec.event_uuid, _rec.event_type,
                        _rec.event_timestamp, _rec.event_invisible, _rec.last_event_id,
                        _rec.diff_file_uuid, _rec.diff_file_size, _rec.rev_diff_file_uuid,
                        _rec.rev_diff_file_size, _rec.file_hash_before_event, _rec.file_hash,
                        _rec.file_name_before_event, _rec.file_name_after_event,
                        _rec.file_size_before_event, _rec.file_size_after_event,
                        _rec.erase_nested, _rec.node_id, _rec.user_id);

                    return next _rec;

                end loop;
                return;
            end;

            \$BODY\$;

            ALTER FUNCTION copy_collaborated(bigint, bigint, character varying)
                OWNER TO {$userName};
        ");


        /* ALTER copy_collaboration_to_user */
        Yii::$app->db->pdo->exec("
            SET search_path TO {$schema};

            CREATE OR REPLACE FUNCTION copy_collaboration_to_user(
                _collaboration_id bigint,
                _user_id bigint,
                _collaboration_name character varying,
                _separator character varying)
                RETURNS SETOF record_copy
                LANGUAGE 'plpgsql'

            AS \$BODY\$

            /*
            Copy collaboration to user.
            The function copies collaborated folder with all nested objects
            to user. Also one event is created/copied for each copied object.
            Args:
            _collaboration_id - collaboration ID that will be copied
            _user_id - user ID to which collaboration will be copied
            _collaboration_name - root folder name for user
            _seperator - path-separator for copied objects
            Returns:
            info for copied files and events as set of record_type
            */

            declare
                _event {$tablePrefix}user_file_events%rowtype;
                _source {$tablePrefix}user_files%rowtype;
                _file record;
                _rec record_copy;
                _node_id bigint;
            begin
                /* check collaboration */
                select f.*
                    from {$tablePrefix}user_files f, {$tablePrefix}user_collaborations c
                    where f.file_uuid = c.file_uuid
                    and f.user_id = c.user_id
                    and f.is_deleted = 0
                    and c.collaboration_status = 1  --active
                    and c.collaboration_id = _collaboration_id  --input parameter
                    and c.user_id <> _user_id  --input parameter
                    into _source;

                if _source is null then
                    raise EXCEPTION 'collaboration with id=% does not exist or is not active',
                    _collaboration_id;
                end if;

                /* pass new name if any */
                if _collaboration_name is not null
                        and trim(_collaboration_name) not in ('', '.','..') then
                    _source.file_name = _collaboration_name;
                end if;

                /* obtain node_id */
                select node_id
                    from {$tablePrefix}user_node
                    where user_id = _user_id
                    order by node_id limit 1
                    into _node_id;

                /* handle */
                for _file in select * from (
                    with recursive obj_tree as (
                        select f1.*,
                            nextval('{$tablePrefix}user_files_file_id_seq') as new_id,
                            _source.file_name::text as file_path
                            from {$tablePrefix}user_files f1
                            where f1.file_id = _source.file_id
                            and f1.is_deleted = 0
                        union all
                        select f2.*,
                            nextval('{$tablePrefix}user_files_file_id_seq') as new_id,
                            concat_ws(_separator, tt.file_path, f2.file_name) as file_path
                            from {$tablePrefix}user_files f2
                            join obj_tree tt on tt.file_id = f2.file_parent_id
                            where f2.is_deleted = 0
                    )
                    select t1.*,
                        coalesce(t2.new_id, 0) as new_parent_id,  -- 0 - into root folder
                        t2.file_uuid as parent_uuid
                    from obj_tree t1
                    left join obj_tree t2 on t1.file_parent_id = t2.file_id
                    order by t1.new_id
                ) as files
                loop
                    _rec = null;

                    -- get last event
                    select * from {$tablePrefix}user_file_events
                        where file_id = _file.file_id  -- use old file_id
                        and event_type <> 2  -- TYPE_DELETE
                        order by event_id desc limit 1
                        into _event;

                    if _event is null then
                        raise EXCEPTION 'last_event for file_id=% not found', _rec.file_id;
                    end if;

                    -- populate record, files part
                    _rec.file_id  = _file.new_id;
                    _rec.file_uuid = _file.file_uuid;  -- from source !!
                    _rec.file_parent_id = _file.new_parent_id;
                    if _rec.file_parent_id = 0 then  -- root folder
                        _rec.file_name = _source.file_name;
                    else
                        _rec.file_name = _file.file_name;
                    end if;
                    _rec.file_size = _file.file_size;
                    _rec.folder_children_count = _file.folder_children_count;
                    _rec.file_md5 = _file.file_md5;
                    _rec.file_created = extract(epoch from now())::bigint;
                    _rec.file_updated = _rec.file_created;
                    _rec.file_lastatime = _rec.file_created;
                    _rec.is_folder = _file.is_folder;
                    _rec.is_deleted = _file.is_deleted;
                    _rec.is_updated = _file.is_updated;
                    _rec.is_outdated = _file.is_outdated;
                    _rec.last_event_type = _file.last_event_type;
                    _rec.diff_file_uuid = _file.diff_file_uuid;
                    _rec.user_id = _user_id;  -- input
                    _rec.node_id = _node_id;
                    _rec.collaboration_id = _collaboration_id;  -- input
                    _rec.is_collaborated = _file.is_collaborated;
                    _rec.is_owner = 0;  -- ??
                    _rec.is_shared = 0;
                    _rec.share_hash = null;
                    _rec.share_group_hash = null;
                    _rec.share_created = null;
                    _rec.share_lifetime = null;
                    _rec.share_ttl_info = null;
                    _rec.share_password = null;
                    _rec.file_path = _file.file_path;
                    _rec.parent_folder_uuid = _file.parent_uuid;

                    -- populate record, events part
                    _rec.event_id = nextval('{$tablePrefix}user_file_events_event_id_seq');
                    if _rec.file_parent_id = 0 then  -- root folder
                        _rec.event_uuid = md5(text(uuid_generate_v4()));  -- not from source
                    else
                        _rec.event_uuid = _event.event_uuid;  -- from source
                    end if;
                    _rec.event_type = _event.event_type;
                    _rec.event_timestamp = _rec.file_created; --?? _event.event_timestamp;
                    _rec.event_invisible = _event.event_invisible;
                    _rec.last_event_id = 0;
                    _rec.diff_file_uuid = _event.diff_file_uuid;
                    _rec.diff_file_size = _event.diff_file_size;
                    _rec.rev_diff_file_uuid = _event.rev_diff_file_uuid;
                    _rec.rev_diff_file_size = _event.rev_diff_file_size;
                    _rec.file_hash_before_event = _event.file_hash_before_event;
                    _rec.file_hash = _event.file_hash;
                    _rec.file_name_before_event = _event.file_name_before_event;
                    _rec.file_name_after_event = _event.file_name_after_event;
                    _rec.file_size_before_event = _event.file_size_before_event;
                    _rec.file_size_after_event = _event.file_size_after_event;
                    if _rec.file_parent_id = 0 then  -- root folder
                        _rec.erase_nested = 1;
                    else
                        _rec.erase_nested = 0;  -- from source
                    end if;

                    -- insert file
                    insert into {$tablePrefix}user_files
                       (file_id, file_parent_id, file_uuid, file_name,
                        file_size, folder_children_count, file_md5, file_created, file_updated,
                        file_lastatime, is_folder, is_deleted, is_updated,
                        is_outdated, last_event_type, diff_file_uuid,
                        user_id, node_id, collaboration_id, is_collaborated,
                        is_owner, is_shared, share_hash, share_group_hash,
                        share_created, share_lifetime, share_ttl_info,
                        share_password)
                    values (_rec.file_id, _rec.file_parent_id, _rec.file_uuid, _rec.file_name,
                        _rec.file_size, _rec.folder_children_count, _rec.file_md5, _rec.file_created, _rec.file_updated,
                        _rec.file_lastatime, _rec.is_folder, _rec.is_deleted, _rec.is_updated,
                        _rec.is_outdated, _rec.last_event_type, _rec.diff_file_uuid,
                        _rec.user_id, _rec.node_id, _rec.collaboration_id, _rec.is_collaborated,
                        _rec.is_owner, _rec.is_shared, _rec.share_hash, _rec.share_group_hash,
                        _rec.share_created, _rec.share_lifetime, _rec.share_ttl_info,
                        _rec.share_password);

                    --insert event
                    insert into {$tablePrefix}user_file_events
                       (file_id, event_id, event_uuid, event_type,
                        event_timestamp, event_invisible, last_event_id,
                        diff_file_uuid, diff_file_size, rev_diff_file_uuid,
                        rev_diff_file_size, file_hash_before_event, file_hash,
                        file_name_before_event, file_name_after_event,
                        file_size_before_event, file_size_after_event,
                        erase_nested, node_id, user_id)
                    values
                       (_rec.file_id, _rec.event_id, _rec.event_uuid, _rec.event_type,
                        _rec.event_timestamp, _rec.event_invisible, _rec.last_event_id,
                        _rec.diff_file_uuid, _rec.diff_file_size, _rec.rev_diff_file_uuid,
                        _rec.rev_diff_file_size, _rec.file_hash_before_event, _rec.file_hash,
                        _rec.file_name_before_event, _rec.file_name_after_event,
                        _rec.file_size_before_event, _rec.file_size_after_event,
                        _rec.erase_nested, _rec.node_id, _rec.user_id);

                    return next _rec;

                end loop;
                return;
            end;

            \$BODY\$;

            ALTER FUNCTION copy_collaboration_to_user(bigint, bigint, character varying, character varying)
                OWNER TO {$userName};
        ");


        /* ALTER copy_files */
        Yii::$app->db->pdo->exec("
            SET search_path TO {$schema};

            CREATE OR REPLACE FUNCTION copy_files(
                id bigint,
                parent_id bigint,
                new_name character varying,
                separator character varying)
                RETURNS SETOF record_copy
                LANGUAGE 'plpgsql'

            AS \$BODY\$

            declare
                _last_event {$tablePrefix}user_file_events%rowtype;
                _parent {$tablePrefix}user_files%rowtype;
                _file record;
                _rec record_copy;
                _parent_id bigint;
                _new_name varchar;
                _parent_full_path text;
                _parent_folder_uuid character varying(32);
            begin

                if parent_id is null or parent_id = 0 then
                    _parent_id = 0;  --copy to root folder
                    _parent_full_path = '';
                    _parent_folder_uuid = null;
                else
                    /* get info for parent folder */
                    select * from {$tablePrefix}user_files where file_id = parent_id into _parent;

                    /* check that destination folder exists */
                    if _parent is null then
                        raise EXCEPTION 'folder with file_id=% does not exist', parent_id;
                    end if;

                    /* check that destination folder is folder (no regular file) */
                    if _parent.is_folder <> 1 then
                        raise EXCEPTION 'file with file_id=% is not folder', parent_id;
                    end if;

                    _parent_id = parent_id;
                    _parent_full_path = concat(get_full_path(_parent_id, separator), separator);
                    _parent_folder_uuid = _parent.file_uuid;
                end if;

                /* check new_name */
                _new_name = null;
                if new_name is not null
                        and trim(new_name) not in ('', '.','..') then
                    _new_name = new_name;
                end if;

                for _file in select * from (
                    with recursive obj_tree as (
                        select
                            file_id,
                            file_parent_id,
                            file_uuid,
                            coalesce(_new_name, file_name) as file_name, --file_name
                            file_size,
                            folder_children_count,
                            file_md5,
                            file_created,
                            file_updated,
                            file_lastatime,
                            is_folder,
                            is_deleted,
                            is_updated,
                            is_outdated,
                            last_event_type,
                            diff_file_uuid,
                            user_id,
                            node_id,
                            collaboration_id,
                            is_collaborated,
                            is_owner,
                            is_shared,
                            share_hash,
                            share_group_hash,
                            share_created,
                            share_lifetime,
                            share_ttl_info,
                            share_password,
                            concat(_parent_full_path, coalesce(_new_name, file_name)) as file_path, --file_path
                            nextval('{$tablePrefix}user_files_file_id_seq') as new_id,
                            md5(text(uuid_generate_v4())) as new_uuid
                        from {$tablePrefix}user_files
                        where file_id = id /* input param, file_id will be copied */
                        and is_deleted = 0
                      union all
                        select
                            t.file_id,
                            t.file_parent_id,
                            t.file_uuid,
                            t.file_name,
                            t.file_size,
                            t.folder_children_count,
                            t.file_md5,
                            t.file_created,
                            t.file_updated,
                            t.file_lastatime,
                            t.is_folder,
                            t.is_deleted,
                            t.is_updated,
                            t.is_outdated,
                            t.last_event_type,
                            t.diff_file_uuid,
                            t.user_id,
                            t.node_id,
                            t.collaboration_id,
                            t.is_collaborated,
                            t.is_owner,
                            t.is_shared,
                            t.share_hash,
                            t.share_group_hash,
                            t.share_created,
                            t.share_lifetime,
                            t.share_ttl_info,
                            t.share_password,
                            concat_ws(separator, ff.file_path, t.file_name) as file_path,
                            nextval('{$tablePrefix}user_files_file_id_seq') as new_id,
                            md5(text(uuid_generate_v4())) as new_uuid
                        from {$tablePrefix}user_files t
                        join obj_tree ff on ff.file_id = t.file_parent_id
                        where t.is_deleted = 0
                    )
                    select
                        t1.new_id,
                        coalesce(t2.new_id, _parent_id) new_parent_id, /* _parent_id from input params or zero */
                        t1.new_uuid,
                        coalesce(t2.new_uuid, _parent_folder_uuid) as parent_folder_uuid,
                        t1.file_path,
                        t1.file_id,
                        t1.file_parent_id,
                        t1.file_uuid,
                        t1.file_name,
                        t1.file_size,
                        t1.folder_children_count,
                        t1.file_md5,
                        t1.file_created,
                        t1.file_updated,
                        t1.file_lastatime,
                        t1.is_folder,
                        t1.is_deleted,
                        t1.is_updated,
                        t1.is_outdated,
                        t1.last_event_type,
                        t1.diff_file_uuid,
                        t1.user_id,
                        t1.node_id,
                        t1.collaboration_id,
                        t1.is_collaborated,
                        t1.is_owner,
                        t1.is_shared,
                        t1.share_hash,
                        t1.share_group_hash,
                        t1.share_created,
                        t1.share_lifetime,
                        t1.share_ttl_info,
                        t1.share_password
                    from obj_tree t1
                    left join obj_tree t2 on t1.file_parent_id = t2.file_id
                    order by t1.new_id

                ) as files
                loop
                    _rec = null;  --clear record;

                    /* populate record, first part (file) */
                    _rec.file_path = _file.file_path;
                    _rec.parent_folder_uuid = _file.parent_folder_uuid;
                    _rec.file_id  = _file.new_id;
                    _rec.file_parent_id = _file.new_parent_id;

                    _rec.file_uuid = _file.new_uuid;
                    _rec.file_name = _file.file_name;
                    _rec.file_size = _file.file_size;
                    _rec.folder_children_count = _file.folder_children_count;
                    _rec.file_md5 = _file.file_md5;
                    _rec.file_created = extract(epoch from now())::bigint;
                    _rec.file_updated = _rec.file_created;
                    _rec.file_lastatime = _rec.file_created;
                    _rec.is_folder = _file.is_folder;
                    _rec.is_deleted = _file.is_deleted;
                    _rec.is_updated = _file.is_updated;
                    _rec.is_outdated = _file.is_outdated;
                    _rec.last_event_type = _file.last_event_type;
                    _rec.diff_file_uuid = _file.diff_file_uuid;
                    _rec.user_id = _file.user_id;
                    _rec.node_id = _file.node_id;

                    if _parent_id = 0 then  --copy to root folder
                        _rec.collaboration_id = null;
                        _rec.is_collaborated = 0;
                        _rec.is_owner = 1;

                        _rec.is_shared = 0;
                            _rec.share_hash = null;
                        _rec.share_group_hash = null;
                        _rec.share_created = null;
                        _rec.share_lifetime = null;
                        _rec.share_ttl_info = null;
                        _rec.share_password = null;
                    else
                        _rec.collaboration_id = _parent.collaboration_id; --from parent
                        _rec.is_collaborated = 0;
                        _rec.is_owner = _parent.is_owner; --from parent

                        _rec.is_shared = 0;
                        if _parent.share_group_hash is not null then
                            _rec.share_hash = md5(concat(_rec.file_uuid, _rec.user_id, _rec.is_deleted));
                        end if;  --else null
                        _rec.share_group_hash = _parent.share_group_hash; --from parent
                        _rec.share_created = _parent.share_created; --from parent
                        _rec.share_lifetime = _parent.share_lifetime; --from parent
                        _rec.share_ttl_info = _parent.share_ttl_info; --from parent
                        _rec.share_password = _parent.share_password; --from parent
                    end if;
                    /* end of first part (file) */

                    /* populate second part (event) */
                    _rec.event_id = nextval('{$tablePrefix}user_file_events_event_id_seq');
                    _rec.event_uuid = md5(text(uuid_generate_v4()));
                    _rec.erase_nested = 0;

                    if _rec.is_folder = 1 then

                        _rec.event_type = 0;  --TYPE_CREATE
                        _rec.event_timestamp = _rec.file_created;
                        _rec.event_invisible = 0;
                        _rec.last_event_id = 0;
                        _rec.diff_file_uuid = null;
                        _rec.diff_file_size = 0;
                        _rec.rev_diff_file_uuid = null;
                        _rec.rev_diff_file_size = 0;
                        _rec.file_hash_before_event = null;
                        _rec.file_hash = null;
                        _rec.file_name_before_event = _rec.file_name;
                        _rec.file_name_after_event = _rec.file_name;
                        _rec.file_size_before_event = 0;
                        _rec.file_size_after_event = 0;

                    elsif _rec.is_folder = 0 then

                        /* get last event */
                        select * from {$tablePrefix}user_file_events
                            where file_id = _file.file_id  --use old file_id
                            and event_type <> 2  --TYPE_DELETE
                            order by event_id desc limit 1
                            into _last_event;

                        if _last_event is null then
                            raise EXCEPTION 'last_event for file_id=% not found', _rec.file_id;
                        end if;

                        _rec.event_type = _last_event.event_type;
                        _rec.event_timestamp = _rec.file_created; --?? _last_event.event_timestamp;
                        _rec.event_invisible = _last_event.event_invisible;
                        _rec.last_event_id = 0;
                        _rec.diff_file_uuid = _last_event.diff_file_uuid;
                        _rec.diff_file_size = _last_event.diff_file_size;
                        _rec.rev_diff_file_uuid = _last_event.rev_diff_file_uuid;
                        _rec.rev_diff_file_size = _last_event.rev_diff_file_size;
                        _rec.file_hash_before_event = _last_event.file_hash_before_event;
                        _rec.file_hash = _last_event.file_hash;
                        _rec.file_name_before_event = _last_event.file_name_before_event;
                        _rec.file_name_after_event = _last_event.file_name_after_event;
                        _rec.file_size_before_event = _last_event.file_size_before_event;
                        _rec.file_size_after_event = _last_event.file_size_after_event;

                    else
                        raise EXCEPTION 'invalid value \"is_folder\" for file_id=%', _rec.file_id;
                    end if;

                    insert into {$tablePrefix}user_files
                       (file_id, file_parent_id, file_uuid, file_name,
                        file_size, folder_children_count, file_md5, file_created, file_updated,
                        file_lastatime, is_folder, is_deleted, is_updated,
                        is_outdated, last_event_type, diff_file_uuid,
                        user_id, node_id, collaboration_id, is_collaborated,
                        is_owner, is_shared, share_hash, share_group_hash,
                        share_created, share_lifetime, share_ttl_info,
                        share_password)
                    values (_rec.file_id, _rec.file_parent_id, _rec.file_uuid, _rec.file_name,
                        _rec.file_size, _rec.folder_children_count, _rec.file_md5, _rec.file_created, _rec.file_updated,
                        _rec.file_lastatime, _rec.is_folder, _rec.is_deleted, _rec.is_updated,
                        _rec.is_outdated, _rec.last_event_type, _rec.diff_file_uuid,
                        _rec.user_id, _rec.node_id, _rec.collaboration_id, _rec.is_collaborated,
                        _rec.is_owner, _rec.is_shared, _rec.share_hash, _rec.share_group_hash,
                        _rec.share_created, _rec.share_lifetime, _rec.share_ttl_info,
                        _rec.share_password);

                    insert into {$tablePrefix}user_file_events
                       (file_id, event_id, event_uuid, event_type,
                        event_timestamp, event_invisible, last_event_id,
                        diff_file_uuid, diff_file_size, rev_diff_file_uuid,
                        rev_diff_file_size, file_hash_before_event, file_hash,
                        file_name_before_event, file_name_after_event,
                        file_size_before_event, file_size_after_event,
                        erase_nested, node_id, user_id)
                    values
                       (_rec.file_id, _rec.event_id, _rec.event_uuid, _rec.event_type,
                        _rec.event_timestamp, _rec.event_invisible, _rec.last_event_id,
                        _rec.diff_file_uuid, _rec.diff_file_size, _rec.rev_diff_file_uuid,
                        _rec.rev_diff_file_size, _rec.file_hash_before_event, _rec.file_hash,
                        _rec.file_name_before_event, _rec.file_name_after_event,
                        _rec.file_size_before_event, _rec.file_size_after_event,
                        _rec.erase_nested, _rec.node_id, _rec.user_id);

                    return next _rec;

                end loop;

                return;

            end;

            \$BODY\$;

            ALTER FUNCTION copy_files(bigint, bigint, character varying, character varying)
                OWNER TO {$userName};
        ");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180119_173801_change_copy_functions cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180119_173801_change_copy_functions cannot be reverted.\n";

        return false;
    }
    */
}
