<?php

use yii\db\Migration;

/**
 * Class m180720_081611_change_function_copy_collaborated
 */
class m180720_081611_change_function_copy_collaborated extends Migration
{
    /**
     * {@inheritdoc}
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

        $this->db->pdo->exec("
            SET search_path TO {$schema};

            -- FUNCTION: copy_collaborated(bigint, bigint, character varying)

            -- DROP FUNCTION copy_collaborated(bigint, bigint, character varying);

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
                    _event_group_timestamp bigint;
                    _event_group_id bigint;
                begin

                    _event_group_timestamp = extract(epoch from now())::bigint;
                    _event_group_id = nextval('{$tablePrefix}user_file_events_group_id_seq');

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
                        _rec.file_created = now()::timestamp;
                        _rec.file_updated = _rec.file_created;
                        _rec.file_lastatime = extract(epoch from _rec.file_created)::bigint;
                        _rec.file_lastmtime = extract(epoch from _rec.file_created)::bigint;
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
                        _rec.event_timestamp = _rec.file_lastatime; --?? _last_event.event_timestamp;
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
                        _rec.parent_before_event = _file.new_parent_id;
                        _rec.parent_after_event = _file.new_parent_id;
                        _rec.event_creator_user_id = _event.event_creator_user_id;
                        _rec.event_creator_node_id = _event.event_creator_node_id;
                        _rec.erase_nested = 0;

                        -- insert file
                        insert into {$tablePrefix}user_files
                           (file_id, file_parent_id, file_uuid, file_name,
                            file_size, folder_children_count, file_md5, file_created, file_updated,
                            file_lastatime, file_lastmtime, is_folder, is_deleted, is_updated,
                            is_outdated, last_event_type, diff_file_uuid,
                            user_id, node_id, collaboration_id, is_collaborated,
                            is_owner, is_shared, share_hash, share_group_hash,
                            share_created, share_lifetime, share_ttl_info,
                            share_password)
                        values (_rec.file_id, _rec.file_parent_id, _rec.file_uuid, _rec.file_name,
                            _rec.file_size, _rec.folder_children_count, _rec.file_md5, _rec.file_created, _rec.file_updated,
                            _rec.file_lastatime, _rec.file_lastmtime, _rec.is_folder, _rec.is_deleted, _rec.is_updated,
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
                            erase_nested, node_id, user_id,
                            parent_before_event, parent_after_event,
                            event_creator_user_id, event_creator_node_id,
                            event_group_timestamp, event_group_id)
                        values
                           (_rec.file_id, _rec.event_id, _rec.event_uuid, _rec.event_type,
                            _rec.event_timestamp, _rec.event_invisible, _rec.last_event_id,
                            _rec.diff_file_uuid, _rec.diff_file_size, _rec.rev_diff_file_uuid,
                            _rec.rev_diff_file_size, _rec.file_hash_before_event, _rec.file_hash,
                            _rec.file_name_before_event, _rec.file_name_after_event,
                            _rec.file_size_before_event, _rec.file_size_after_event,
                            _rec.erase_nested, _rec.node_id, _rec.user_id,
                            _rec.parent_before_event, _rec.parent_after_event,
                            _rec.event_creator_user_id, _rec.event_creator_node_id,
                            _event_group_timestamp, _event_group_id);

                        return next _rec;

                    end loop;
                    return;
                end;


            \$BODY\$;

            ALTER FUNCTION copy_collaborated(bigint, bigint, character varying)
                OWNER TO {$userName};

        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180720_081611_change_function_copy_collaborated cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180720_081611_change_function_copy_collaborated cannot be reverted.\n";

        return false;
    }
    */
}
