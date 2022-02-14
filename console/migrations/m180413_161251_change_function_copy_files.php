<?php

use yii\db\Migration;

/**
 * Class m180413_161251_change_function_copy_files
 */
class m180413_161251_change_function_copy_files extends Migration
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

            -- FUNCTION: copy_files(bigint, bigint, character varying, character varying, bigint, character varying, boolean)

            DROP FUNCTION copy_files(bigint, bigint, character varying, character varying, bigint, character varying, boolean);

            CREATE OR REPLACE FUNCTION copy_files(
                id bigint,
                parent_id bigint,
                for_user_id bigint,
                new_name character varying,
                separator character varying,
                max_timestamp bigint,
                event_uuid_from_node character varying,
                _is_debug boolean)
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
                        SELECT * FROM {$tablePrefix}user_files WHERE file_id = parent_id INTO _parent;

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

                    for _file in SELECT * FROM (
                        WITH RECURSIVE obj_tree as (
                            SELECT * FROM (
                                SELECT DISTINCT ON (e1.file_id)
                                    --f1.file_id,
                                    e1.parent_after_event as file_parent_id,
                                    f1.file_parent_id as current_file_parent_id,
                                    f1.file_uuid,
                                    coalesce(_new_name, e1.file_name_after_event) as file_name, --file_name
                                    e1.file_size_after_event as file_size,
                                    f1.folder_children_count,
                                    (CASE WHEN (e1.file_hash IS NOT NULL) THEN e1.file_hash ELSE f1.file_md5 END) as file_md5,
                                    f1.file_created,
                                    f1.file_updated,
                                    f1.file_lastatime,
                                    f1.is_folder,
                                    f1.is_deleted,
                                    f1.is_updated,
                                    f1.is_outdated,
                                    f1.last_event_type,
                                    --diff_file_uuid,
                                    --f1.user_id,
                                    --f1.node_id,
                                    f1.collaboration_id,
                                    f1.is_collaborated,
                                    f1.is_owner,
                                    f1.is_shared,
                                    f1.share_hash,
                                    f1.share_group_hash,
                                    f1.share_created,
                                    f1.share_lifetime,
                                    f1.share_ttl_info,
                                    f1.share_password,
                                    concat(_parent_full_path, coalesce(_new_name, e1.file_name_after_event)) as file_path, --file_path
                                    nextval('{$tablePrefix}user_files_file_id_seq') as new_id,
                                    md5(text(uuid_generate_v4())) as new_uuid,
                                    e1.*
                                FROM {$tablePrefix}user_file_events as e1
                                INNER JOIN {$tablePrefix}user_files as f1 ON f1.file_id = e1.file_id
                                WHERE e1.event_timestamp < max_timestamp
                                AND f1.file_id = id /* input param, file_id will be copied */ --???????????????????????????????????????????????????????????
                                AND f1.user_id = for_user_id
                                ORDER BY e1.file_id, e1.event_id desc
                            ) as parents
                            WHERE parents.event_type <> 2
                            --AND is_deleted = 0
                        UNION ALL
                            SELECT * FROM (
                                SELECT distinct on (e2.file_id)
                                    --f2.file_id,
                                    e2.parent_after_event as file_parent_id,
                                    f2.file_parent_id as current_file_parent_id,
                                    f2.file_uuid,
                                    (CASE WHEN (e2.file_name_after_event <> '') THEN e2.file_name_after_event ELSE f2.file_name END) as file_name,
                                    e2.file_size_after_event as file_size,
                                    f2.folder_children_count,
                                    (CASE WHEN (e2.file_hash IS NOT NULL) THEN e2.file_hash ELSE f2.file_md5 END) as file_md5,
                                    f2.file_created,
                                    f2.file_updated,
                                    f2.file_lastatime,
                                    f2.is_folder,
                                    f2.is_deleted,
                                    f2.is_updated,
                                    f2.is_outdated,
                                    f2.last_event_type,
                                    --f2.diff_file_uuid,
                                    --f2.user_id,
                                    --f2.node_id,
                                    f2.collaboration_id,
                                    f2.is_collaborated,
                                    f2.is_owner,
                                    f2.is_shared,
                                    f2.share_hash,
                                    f2.share_group_hash,
                                    f2.share_created,
                                    f2.share_lifetime,
                                    f2.share_ttl_info,
                                    f2.share_password,
                                    concat_ws(separator, ff.file_path, (CASE WHEN (e2.file_name_after_event <> '') THEN e2.file_name_after_event ELSE f2.file_name END)) as file_path,
                                    nextval('{$tablePrefix}user_files_file_id_seq') as new_id,
                                    md5(text(uuid_generate_v4())) as new_uuid,
                                    e2.*
                                FROM {$tablePrefix}user_file_events as e2
                                INNER JOIN {$tablePrefix}user_files as f2 ON f2.file_id = e2.file_id
                                INNER JOIN obj_tree as ff ON ff.file_id = e2.parent_after_event --f2.file_parent_id
                                WHERE e2.event_timestamp < max_timestamp
                                AND f2.user_id = for_user_id
                                ORDER BY e2.file_id, e2.event_id DESC
                            ) as children
                            WHERE children.event_type <> 2
                            --WHERE t.is_deleted = 0
                        )
                        SELECT
                            t1.*,
                            coalesce(t2.new_id, _parent_id) as new_parent_id, /* _parent_id from input params or zero */
                            coalesce(t2.new_uuid, _parent_folder_uuid) as parent_folder_uuid
                        FROM obj_tree t1
                        LEFT JOIN obj_tree t2 ON t1.file_parent_id = t2.file_id
                        ORDER BY t1.new_id
                    ) as files
                    --@@--
                    --строка в выборке содержит все поля из obj_tree + все поля из obj_events (т.е. все поля последнего события по этому файлу)
                    loop

                        _rec = null;  --clear record;
                        _last_event = null; --clear _last_event

                        /*
                        Если ид перента в записи файла не совпадает с ид перента в записи евента - значит что после этого евента было перемещение.
                        Нужно выяснить было ли это переещение до max_timestamp или после.
                        И если это событие перемещения было до max_timestamp то такой файл не должен попасть в копию
                        */
                        if _file.file_parent_id <> _file.current_file_parent_id then
                            /*
                            raise EXCEPTION 'SELECT * FROM {$tablePrefix}user_file_events
                            WHERE file_id = %
                            AND event_type = 3
                            AND event_timestamp > %
                            AND event_timestamp <= %
                            ORDER BY event_id ASC
                            LIMIT 1
                            INTO _last_event', _file.file_id, _file.event_timestamp, max_timestamp;
                            */
                            SELECT * FROM {$tablePrefix}user_file_events
                            WHERE file_id = _file.file_id  --use old file_id
                            AND event_type = 3  --TYPE_MOVE
                            AND event_timestamp > _file.event_timestamp
                            AND event_timestamp <= max_timestamp
                            ORDER BY event_id ASC
                            LIMIT 1
                            INTO _last_event;

                            if _last_event.event_id is not null then
                                CONTINUE;
                            end if;
                        end if;

                        /* populate record, first part (file) */
                        _rec.file_path = _file.file_path;
                        _rec.parent_folder_uuid = _file.parent_folder_uuid;
                        _rec.file_id  = _file.new_id;
                        _rec.file_parent_id = _file.new_parent_id;

                        _rec.file_uuid = _file.new_uuid;
                        _rec.file_name = CASE WHEN ((_file.file_id != id) AND (_file.file_name_after_event != '')) THEN _file.file_name_after_event ELSE _file.file_name END;
                        _rec.file_size = _file.file_size_after_event;
                        _rec.folder_children_count = _file.folder_children_count;
                        _rec.file_md5 = CASE WHEN ((_file.file_id != id) AND (_file.file_hash != '') AND (_file.file_hash IS NOT NULL)) THEN _file.file_hash ELSE _file.file_md5 END;
                        _rec.file_created = now()::timestamp;
                        _rec.file_updated = _rec.file_created;
                        _rec.file_lastatime = extract(epoch from _rec.file_created)::bigint;
                        _rec.is_folder = _file.is_folder;
                        _rec.is_deleted = 0; --_file.is_deleted;
                        _rec.is_updated = 0; --_file.is_updated;
                        _rec.is_outdated = 0; --_file.is_outdated;
                        _rec.last_event_type = 0; --_file.last_event_type;
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

                        /* Если совпадает это значит что это главный копирууемый элемент
                         * и если в функцию был передан event_uuid который не null
                         * значит нужно его присвоить евенту а не генерить новый */
                        if ((_parent_id = _file.new_parent_id) and (event_uuid_from_node is not null)) then
                            _rec.event_uuid = event_uuid_from_node;
                        else
                            _rec.event_uuid = md5(text(uuid_generate_v4()));
                        end if;
                        _rec.erase_nested = 0;
                        _rec.parent_before_event = _file.new_parent_id;
                        _rec.parent_after_event = _file.new_parent_id;

                        if _rec.is_folder = 1 then

                            _rec.event_type = 0;  --TYPE_CREATE
                            _rec.event_timestamp = _rec.file_lastatime;
                            _rec.event_invisible = 0;
                            _rec.last_event_id = 0;
                            _rec.diff_file_uuid = null;
                            _rec.diff_file_size = 0;
                            _rec.rev_diff_file_uuid = null;
                            _rec.rev_diff_file_size = 0;
                            _rec.file_hash_before_event = null;
                            _rec.file_hash = null;
                            _rec.file_name_before_event = ''; --@@-- event
                            _rec.file_name_after_event = _rec.file_name; --@@-- event
                            _rec.file_size_before_event = 0;
                            _rec.file_size_after_event = 0;

                        elsif _rec.is_folder = 0 then

                            _rec.event_type = 0;  --TYPE_CREATE --_last_event.event_type;
                            _rec.event_timestamp = _rec.file_lastatime; --?? _last_event.event_timestamp;
                            _rec.event_invisible = 0; --_last_event.event_invisible;
                            _rec.last_event_id = 0;
                            _rec.diff_file_uuid = _file.diff_file_uuid; --@@-- event
                            _rec.diff_file_size = _file.diff_file_size; --@@-- event
                            _rec.rev_diff_file_uuid = _file.rev_diff_file_uuid; --@@-- event
                            _rec.rev_diff_file_size = _file.rev_diff_file_size; --@@-- event
                            _rec.file_hash_before_event = _file.file_hash_before_event; --@@-- event
                            _rec.file_hash = _rec.file_md5; --@@-- event
                            _rec.file_name_before_event = ''; --@@-- event
                            _rec.file_name_after_event = _rec.file_name; --@@-- event
                            _rec.file_size_before_event = _rec.file_size; --@@-- event
                            _rec.file_size_after_event = _rec.file_size; --@@-- event

                        else
                            raise EXCEPTION 'invalid value \"is_folder\" for file_id=%', _rec.file_id;
                        end if;

                        if (_is_debug = false) then

                            INSERT INTO {$tablePrefix}user_files
                                (file_id, file_parent_id, file_uuid, file_name,
                                file_size, folder_children_count, file_md5, file_created, file_updated,
                                file_lastatime, is_folder, is_deleted, is_updated,
                                is_outdated, last_event_type, diff_file_uuid,
                                user_id, node_id, collaboration_id, is_collaborated,
                                is_owner, is_shared, share_hash, share_group_hash,
                                share_created, share_lifetime, share_ttl_info,
                                share_password)
                            VALUES
                                (_rec.file_id, _rec.file_parent_id, _rec.file_uuid, _rec.file_name,
                                _rec.file_size, _rec.folder_children_count, _rec.file_md5, _rec.file_created, _rec.file_updated,
                                _rec.file_lastatime, _rec.is_folder, _rec.is_deleted, _rec.is_updated,
                                _rec.is_outdated, _rec.last_event_type, _rec.diff_file_uuid,
                                _rec.user_id, _rec.node_id, _rec.collaboration_id, _rec.is_collaborated,
                                _rec.is_owner, _rec.is_shared, _rec.share_hash, _rec.share_group_hash,
                                _rec.share_created, _rec.share_lifetime, _rec.share_ttl_info,
                                _rec.share_password);

                            INSERT INTO {$tablePrefix}user_file_events
                                (file_id, event_id, event_uuid, event_type,
                                event_timestamp, event_invisible, last_event_id,
                                diff_file_uuid, diff_file_size, rev_diff_file_uuid,
                                rev_diff_file_size, file_hash_before_event, file_hash,
                                file_name_before_event, file_name_after_event,
                                file_size_before_event, file_size_after_event,
                                erase_nested, node_id, user_id,
                                parent_before_event, parent_after_event)
                            VALUES
                                (_rec.file_id, _rec.event_id, _rec.event_uuid, _rec.event_type,
                                _rec.event_timestamp, _rec.event_invisible, _rec.last_event_id,
                                _rec.diff_file_uuid, _rec.diff_file_size, _rec.rev_diff_file_uuid,
                                _rec.rev_diff_file_size, _rec.file_hash_before_event, _rec.file_hash,
                                _rec.file_name_before_event, _rec.file_name_after_event,
                                _rec.file_size_before_event, _rec.file_size_after_event,
                                _rec.erase_nested, _rec.node_id, _rec.user_id,
                                _rec.parent_before_event, _rec.parent_after_event);

                        end if;

                        return next _rec;

                    end loop;

                    return;

                end;

            \$BODY\$;

            ALTER FUNCTION copy_files(bigint, bigint, bigint, character varying, character varying, bigint, character varying, boolean)
                OWNER TO {$userName};
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180413_161251_change_function_copy_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180413_161251_change_function_copy_files cannot be reverted.\n";

        return false;
    }
    */
}
