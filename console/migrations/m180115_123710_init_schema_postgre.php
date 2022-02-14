<?php

use yii\db\Migration;

/**
 * Class m180115_114152_init_schema_postgre
 */
class m180115_123710_init_schema_postgre extends Migration
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

        $this->db->pdo->exec("
            --
            -- PostgreSQL database dump
            --

            -- Dumped from database version 9.6.6
            -- Dumped by pg_dump version 9.6.6

            SET statement_timeout = 0;
            SET lock_timeout = 0;
            SET idle_in_transaction_session_timeout = 0;
            SET client_encoding = 'UTF8';
            SET standard_conforming_strings = on;
            SET check_function_bodies = false;
            SET client_min_messages = warning;
            SET row_security = off;

            --
            -- Name: {$schema}; Type: SCHEMA; Schema: -; Owner: -
            --

            SET search_path = {$schema};

            --
            -- Name: {$tablePrefix}remote_actions_action_type; Type: TYPE; Schema: {$schema}; Owner: -
            --

            CREATE TYPE {$tablePrefix}remote_actions_action_type AS ENUM (
                'logout',
                'wipe'
            );


            --
            -- Name: {$tablePrefix}servers_server_type; Type: TYPE; Schema: {$schema}; Owner: -
            --

            CREATE TYPE {$tablePrefix}servers_server_type AS ENUM (
                'TURN',
                'STUN',
                'SIGN',
                'PROXY'
            );


            --
            -- Name: {$tablePrefix}software_software_program_type; Type: TYPE; Schema: {$schema}; Owner: -
            --

            CREATE TYPE {$tablePrefix}software_software_program_type AS ENUM (
                'file',
                'url'
            );


            --
            -- Name: {$tablePrefix}software_software_type; Type: TYPE; Schema: {$schema}; Owner: -
            --

            CREATE TYPE {$tablePrefix}software_software_type AS ENUM (
                'windows',
                'linux',
                'mac',
                'ios',
                'android'
            );


            --
            -- Name: {$tablePrefix}user_colleagues_colleague_permission; Type: TYPE; Schema: {$schema}; Owner: -
            --

            CREATE TYPE {$tablePrefix}user_colleagues_colleague_permission AS ENUM (
                'view',
                'edit',
                'owner'
            );


            --
            -- Name: {$tablePrefix}user_colleagues_colleague_status; Type: TYPE; Schema: {$schema}; Owner: -
            --

            CREATE TYPE {$tablePrefix}user_colleagues_colleague_status AS ENUM (
                'joined',
                'invited',
                'queued',
                'queued_add',
                'queued_del'
            );


            --
            -- Name: {$tablePrefix}user_node_node_devicetype; Type: TYPE; Schema: {$schema}; Owner: -
            --

            CREATE TYPE {$tablePrefix}user_node_node_devicetype AS ENUM (
                'desktop',
                'phone',
                'tablet',
                'browser'
            );


            --
            -- Name: {$tablePrefix}user_node_node_ostype; Type: TYPE; Schema: {$schema}; Owner: -
            --

            CREATE TYPE {$tablePrefix}user_node_node_ostype AS ENUM (
                'WebFM',
                'Windows',
                'Darwin',
                'Linux',
                'iOS',
                'Android'
            );


            --
            -- Name: record_copy; Type: TYPE; Schema: {$schema}; Owner: -
            --

            CREATE TYPE record_copy AS (
                file_id bigint,
                file_parent_id bigint,
                file_uuid character varying(32),
                file_name character varying(255),
                file_size bigint,
                file_md5 character varying(32),
                file_created bigint,
                file_updated bigint,
                file_lastatime bigint,
                is_folder smallint,
                is_deleted smallint,
                is_updated smallint,
                is_outdated bigint,
                last_event_type smallint,
                user_id bigint,
                node_id bigint,
                collaboration_id bigint,
                is_collaborated smallint,
                is_owner smallint,
                is_shared smallint,
                share_hash character varying(32),
                share_group_hash character varying(32),
                share_created timestamp with time zone,
                share_lifetime timestamp with time zone,
                share_ttl_info bigint,
                share_password character varying(32),
                event_id bigint,
                event_uuid character varying(32),
                event_type smallint,
                event_timestamp bigint,
                event_invisible smallint,
                last_event_id bigint,
                diff_file_uuid character varying(32),
                diff_file_size bigint,
                rev_diff_file_uuid character varying(32),
                rev_diff_file_size bigint,
                file_hash_before_event character varying(32),
                file_hash character varying(32),
                file_name_before_event character varying(255),
                file_name_after_event character varying(255),
                file_size_before_event bigint,
                file_size_after_event bigint,
                erase_nested smallint,
                file_path text,
                parent_folder_uuid character varying(32)
            );


            --
            -- Name: copy_collaborated(bigint, bigint, character varying); Type: FUNCTION; Schema: {$schema}; Owner: -
            --

            CREATE FUNCTION copy_collaborated(id bigint, parent_id bigint, separator character varying) RETURNS SETOF record_copy
                LANGUAGE plpgsql
                AS $$

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
                        file_size, file_md5, file_created, file_updated,
                        file_lastatime, is_folder, is_deleted, is_updated,
                        is_outdated, last_event_type, diff_file_uuid,
                        user_id, node_id, collaboration_id, is_collaborated,
                        is_owner, is_shared, share_hash, share_group_hash,
                        share_created, share_lifetime, share_ttl_info,
                        share_password)
                    values (_rec.file_id, _rec.file_parent_id, _rec.file_uuid, _rec.file_name,
                        _rec.file_size, _rec.file_md5, _rec.file_created, _rec.file_updated,
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

            $$;


            --
            -- Name: copy_collaboration_to_user(bigint, bigint, character varying, character varying); Type: FUNCTION; Schema: {$schema}; Owner: -
            --

            CREATE FUNCTION copy_collaboration_to_user(_collaboration_id bigint, _user_id bigint, _collaboration_name character varying, _separator character varying) RETURNS SETOF record_copy
                LANGUAGE plpgsql
                AS $$
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
                        file_size, file_md5, file_created, file_updated,
                        file_lastatime, is_folder, is_deleted, is_updated,
                        is_outdated, last_event_type, diff_file_uuid,
                        user_id, node_id, collaboration_id, is_collaborated,
                        is_owner, is_shared, share_hash, share_group_hash,
                        share_created, share_lifetime, share_ttl_info,
                        share_password)
                    values (_rec.file_id, _rec.file_parent_id, _rec.file_uuid, _rec.file_name,
                        _rec.file_size, _rec.file_md5, _rec.file_created, _rec.file_updated,
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

            $$;


            --
            -- Name: copy_files(bigint, bigint, character varying, character varying); Type: FUNCTION; Schema: {$schema}; Owner: -
            --

            CREATE FUNCTION copy_files(id bigint, parent_id bigint, new_name character varying, separator character varying) RETURNS SETOF record_copy
                LANGUAGE plpgsql
                AS $$

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

                    /* populate record, {$schema} part (file) */
                    _rec.file_path = _file.file_path;
                    _rec.parent_folder_uuid = _file.parent_folder_uuid;
                    _rec.file_id  = _file.new_id;
                    _rec.file_parent_id = _file.new_parent_id;

                    _rec.file_uuid = _file.new_uuid;
                    _rec.file_name = _file.file_name;
                    _rec.file_size = _file.file_size;
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
                    /* end of {$schema} part (file) */

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
                        file_size, file_md5, file_created, file_updated,
                        file_lastatime, is_folder, is_deleted, is_updated,
                        is_outdated, last_event_type, diff_file_uuid,
                        user_id, node_id, collaboration_id, is_collaborated,
                        is_owner, is_shared, share_hash, share_group_hash,
                        share_created, share_lifetime, share_ttl_info,
                        share_password)
                    values (_rec.file_id, _rec.file_parent_id, _rec.file_uuid, _rec.file_name,
                        _rec.file_size, _rec.file_md5, _rec.file_created, _rec.file_updated,
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

            $$;


            --
            -- Name: get_full_path(bigint, character varying); Type: FUNCTION; Schema: {$schema}; Owner: -
            --

            CREATE FUNCTION get_full_path(by_file_id bigint, separator character varying) RETURNS text
                LANGUAGE plpgsql
                AS $$

            declare
                full_path text;

            BEGIN
                    with recursive obj_tree as (
                                SELECT
                                    file_id,
                                    file_parent_id,
                                    text(file_name) AS file_name
                                FROM {$tablePrefix}user_files
                                WHERE file_id = $1
                              UNION ALL
                                SELECT
                                    t.file_id,
                                    t.file_parent_id,
                                    concat_ws($2, t.file_name, ff.file_name)
                                FROM {$tablePrefix}user_files AS t
                                JOIN obj_tree ff on ff.file_parent_id = t.file_id
                            )
                    SELECT file_name  INTO full_path FROM obj_tree WHERE file_parent_id=0;
                    RETURN full_path;
            END;

            $$;


            --
            -- Name: uuid_short(); Type: FUNCTION; Schema: {$schema}; Owner: -
            --

            CREATE FUNCTION uuid_short() RETURNS character varying
                LANGUAGE plpgsql
                AS $$
            declare
            begin
              return uuid_generate_v4();
            end;
            $$;


            SET default_tablespace = '';

            SET default_with_oids = false;


            --
            -- Name: {$tablePrefix}admins; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}admins (
                admin_id integer NOT NULL,
                admin_name character varying(50) NOT NULL,
                admin_email character varying(50) NOT NULL,
                auth_key character varying(32) NOT NULL,
                password_hash character varying(255) NOT NULL,
                password_reset_token character varying(255),
                admin_created timestamp with time zone NOT NULL,
                admin_updated timestamp with time zone NOT NULL,
                admin_status smallint DEFAULT 0 NOT NULL
            );


            --
            -- Name: {$tablePrefix}admins_admin_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}admins_admin_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}admins_admin_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}admins_admin_id_seq OWNED BY {$tablePrefix}admins.admin_id;


            --
            -- Name: {$tablePrefix}colleagues_reports; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}colleagues_reports (
                report_id bigint NOT NULL,
                report_date timestamp with time zone NOT NULL,
                report_timestamp bigint NOT NULL,
                report_isnew smallint DEFAULT 1 NOT NULL,
                collaboration_id bigint DEFAULT '0'::bigint NOT NULL,
                colleague_id bigint DEFAULT '0'::bigint NOT NULL,
                file_id bigint DEFAULT '0'::bigint NOT NULL,
                file_parent_id bigint,
                file_parent_id_before_event bigint,
                file_name_after_event character varying(255) DEFAULT ''::character varying NOT NULL,
                file_name_before_event character varying(255) DEFAULT ''::character varying NOT NULL,
                parent_folder_name_after_event character varying(255) DEFAULT ''::character varying NOT NULL,
                parent_folder_name_before_event character varying(255) DEFAULT ''::character varying NOT NULL,
                file_renamed smallint DEFAULT 0 NOT NULL,
                file_moved smallint DEFAULT 0 NOT NULL,
                is_folder smallint DEFAULT 0 NOT NULL,
                event_type smallint DEFAULT '0'::smallint NOT NULL,
                owner_user_id bigint DEFAULT '0'::bigint NOT NULL,
                colleague_user_id bigint DEFAULT '0'::bigint NOT NULL,
                colleague_user_email character varying(50) DEFAULT ''::character varying NOT NULL,
                colleague_node_id bigint DEFAULT '0'::bigint NOT NULL
            );


            --
            -- Name: TABLE {$tablePrefix}colleagues_reports; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON TABLE {$tablePrefix}colleagues_reports IS 'Таблица событий с файлами';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.report_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.report_id IS 'ID';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.report_date; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.report_date IS 'Дата события';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.report_timestamp; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.report_timestamp IS 'Таймстамп события';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.collaboration_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.collaboration_id IS 'Ид коллаборации';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.colleague_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.colleague_id IS 'Ид коллеги коллаборации';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.file_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.file_id IS 'id изменяемого файла';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.file_parent_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.file_parent_id IS 'Ид родительского каталога';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.file_parent_id_before_event; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.file_parent_id_before_event IS 'Ид родительского каталога до евента (перемещения)';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.file_name_after_event; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.file_name_after_event IS 'имя файла после выполнения евента';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.file_name_before_event; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.file_name_before_event IS 'имя файла до выполнения евента';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.parent_folder_name_after_event; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.parent_folder_name_after_event IS 'имя родительской папки после выполнения евента';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.parent_folder_name_before_event; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.parent_folder_name_before_event IS 'имя родительской папки до выполнения евента';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.event_type; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.event_type IS 'тип события';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.owner_user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.owner_user_id IS 'Идентификатор user_id владельца файла';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.colleague_user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.colleague_user_id IS 'Идентификатор user_id коллеги создающего/изменяющего/удаляющего файла';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.colleague_user_email; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.colleague_user_email IS 'Емейл коллеги создающего/изменяющего/удаляющего файл';


            --
            -- Name: COLUMN {$tablePrefix}colleagues_reports.colleague_node_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}colleagues_reports.colleague_node_id IS 'Идентификатор node_id целевой ноды коллеги';


            --
            -- Name: {$tablePrefix}colleagues_reports_report_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}colleagues_reports_report_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}colleagues_reports_report_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}colleagues_reports_report_id_seq OWNED BY {$tablePrefix}colleagues_reports.report_id;


            --
            -- Name: {$tablePrefix}licenses; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}licenses (
                license_id bigint NOT NULL,
                license_type character varying(20) NOT NULL,
                license_description character varying(255) NOT NULL,
                license_limit_bytes bigint DEFAULT '0'::bigint NOT NULL,
                license_limit_days smallint DEFAULT '0'::smallint NOT NULL,
                license_limit_nodes smallint DEFAULT '0'::smallint NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}licenses.license_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}licenses.license_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}licenses.license_type; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}licenses.license_type IS 'Тип лицензии';


            --
            -- Name: COLUMN {$tablePrefix}licenses.license_description; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}licenses.license_description IS 'Описание лицензии';


            --
            -- Name: COLUMN {$tablePrefix}licenses.license_limit_bytes; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}licenses.license_limit_bytes IS 'Количество разрешенных байт. Если 0 то нет ограничения.';


            --
            -- Name: COLUMN {$tablePrefix}licenses.license_limit_days; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}licenses.license_limit_days IS 'Количество бесплатных дней. Если 0 то нет ограничения.';


            --
            -- Name: COLUMN {$tablePrefix}licenses.license_limit_nodes; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}licenses.license_limit_nodes IS 'Количество доступных нод. Если 0 то нет ограничения.';


            --
            -- Name: {$tablePrefix}licenses_license_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}licenses_license_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}licenses_license_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}licenses_license_id_seq OWNED BY {$tablePrefix}licenses.license_id;


            --
            -- Name: {$tablePrefix}mail_templates; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}mail_templates (
                template_id bigint NOT NULL,
                template_key character varying(30) DEFAULT ''::character varying NOT NULL,
                template_lang character varying(3) DEFAULT ''::character varying NOT NULL,
                template_from_email character varying(50) DEFAULT ''::character varying NOT NULL,
                template_from_name character varying(20) DEFAULT ''::character varying NOT NULL,
                template_subject character varying(255) DEFAULT ''::character varying NOT NULL,
                template_body_html text,
                template_body_text text
            );


            --
            -- Name: COLUMN {$tablePrefix}mail_templates.template_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}mail_templates.template_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}mail_templates.template_key; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}mail_templates.template_key IS 'Variant of template';


            --
            -- Name: COLUMN {$tablePrefix}mail_templates.template_lang; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}mail_templates.template_lang IS 'Language of template';


            --
            -- Name: COLUMN {$tablePrefix}mail_templates.template_from_email; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}mail_templates.template_from_email IS 'Email of sender for  template';


            --
            -- Name: COLUMN {$tablePrefix}mail_templates.template_from_name; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}mail_templates.template_from_name IS 'Name of sender for template';


            --
            -- Name: COLUMN {$tablePrefix}mail_templates.template_subject; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}mail_templates.template_subject IS 'Subject of template';


            --
            -- Name: COLUMN {$tablePrefix}mail_templates.template_body_html; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}mail_templates.template_body_html IS 'Message body HTML';


            --
            -- Name: COLUMN {$tablePrefix}mail_templates.template_body_text; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}mail_templates.template_body_text IS 'Message body TEXT';


            --
            -- Name: {$tablePrefix}mail_templates_template_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}mail_templates_template_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}mail_templates_template_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}mail_templates_template_id_seq OWNED BY {$tablePrefix}mail_templates.template_id;


            --
            -- Name: {$tablePrefix}news; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}news (
                news_id bigint NOT NULL,
                news_name character varying(255) NOT NULL,
                news_text text NOT NULL,
                news_status smallint DEFAULT 0 NOT NULL,
                news_created timestamp with time zone NOT NULL,
                news_updated timestamp with time zone NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}news.news_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}news.news_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}news.news_name; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}news.news_name IS 'Заголовок новости';


            --
            -- Name: COLUMN {$tablePrefix}news.news_text; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}news.news_text IS 'Текст новости';


            --
            -- Name: COLUMN {$tablePrefix}news.news_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}news.news_created IS 'Дата создания';


            --
            -- Name: COLUMN {$tablePrefix}news.news_updated; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}news.news_updated IS 'Дата изменения';


            --
            -- Name: {$tablePrefix}news_news_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}news_news_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}news_news_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}news_news_id_seq OWNED BY {$tablePrefix}news.news_id;


            --
            -- Name: {$tablePrefix}node_changes; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}node_changes (
                ncg_id bigint NOT NULL,
                node_hash character varying(128) NOT NULL,
                ncg_json text,
                ncg_patch bigint DEFAULT '0'::bigint NOT NULL,
                ncg_sended smallint DEFAULT 0 NOT NULL,
                user_id bigint DEFAULT '0'::bigint NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}node_changes.ncg_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}node_changes.ncg_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}node_changes.node_hash; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}node_changes.node_hash IS 'NodeHash';


            --
            -- Name: COLUMN {$tablePrefix}node_changes.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}node_changes.user_id IS 'UserId';


            --
            -- Name: {$tablePrefix}node_changes_ncg_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}node_changes_ncg_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}node_changes_ncg_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}node_changes_ncg_id_seq OWNED BY {$tablePrefix}node_changes.ncg_id;


            --
            -- Name: {$tablePrefix}notifications; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}notifications (
                notif_id bigint NOT NULL,
                notif_isnew smallint DEFAULT 0 NOT NULL,
                notif_text character varying(255) DEFAULT ''::character varying NOT NULL,
                notif_date timestamp with time zone,
                user_id bigint DEFAULT '0'::bigint NOT NULL
            );


            --
            -- Name: TABLE {$tablePrefix}notifications; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON TABLE {$tablePrefix}notifications IS 'Таблица для хранения сообщений пользователям';


            --
            -- Name: COLUMN {$tablePrefix}notifications.notif_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}notifications.notif_id IS 'ID';


            --
            -- Name: COLUMN {$tablePrefix}notifications.notif_text; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}notifications.notif_text IS 'Текст сообщения';


            --
            -- Name: COLUMN {$tablePrefix}notifications.notif_date; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}notifications.notif_date IS 'Дата сообщения.';


            --
            -- Name: COLUMN {$tablePrefix}notifications.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}notifications.user_id IS 'Идентификатор user_id для ускорения отбора по пользователю';


            --
            -- Name: {$tablePrefix}notifications_notif_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}notifications_notif_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}notifications_notif_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}notifications_notif_id_seq OWNED BY {$tablePrefix}notifications.notif_id;


            --
            -- Name: {$tablePrefix}pages; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}pages (
                page_id bigint NOT NULL,
                page_created timestamp with time zone NOT NULL,
                page_updated timestamp with time zone NOT NULL,
                page_status smallint DEFAULT 0 NOT NULL,
                page_lang character varying(3) DEFAULT ''::character varying NOT NULL,
                page_title character varying(255) DEFAULT ''::character varying NOT NULL,
                page_name character varying(100) DEFAULT ''::character varying NOT NULL,
                page_alias character varying(255) DEFAULT ''::character varying NOT NULL,
                page_keywords character varying(255) DEFAULT ''::character varying NOT NULL,
                page_description character varying(255) DEFAULT ''::character varying NOT NULL,
                page_text text
            );


            --
            -- Name: COLUMN {$tablePrefix}pages.page_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pages.page_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}pages.page_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pages.page_created IS 'Создано';


            --
            -- Name: COLUMN {$tablePrefix}pages.page_updated; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pages.page_updated IS 'Обновлено';


            --
            -- Name: COLUMN {$tablePrefix}pages.page_lang; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pages.page_lang IS 'Язык страницы';


            --
            -- Name: COLUMN {$tablePrefix}pages.page_title; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pages.page_title IS 'SEO-title';


            --
            -- Name: COLUMN {$tablePrefix}pages.page_name; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pages.page_name IS 'Название';


            --
            -- Name: COLUMN {$tablePrefix}pages.page_alias; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pages.page_alias IS 'ЧПУ';


            --
            -- Name: COLUMN {$tablePrefix}pages.page_keywords; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pages.page_keywords IS 'SEO-keywords';


            --
            -- Name: COLUMN {$tablePrefix}pages.page_description; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pages.page_description IS 'SEO-description';


            --
            -- Name: COLUMN {$tablePrefix}pages.page_text; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pages.page_text IS 'HTML-Контент';


            --
            -- Name: {$tablePrefix}pages_page_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}pages_page_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}pages_page_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}pages_page_id_seq OWNED BY {$tablePrefix}pages.page_id;


            --
            -- Name: {$tablePrefix}paypal_pays; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}paypal_pays (
                pp_id bigint NOT NULL,
                pp_payment_id character varying(30) DEFAULT ''::character varying NOT NULL,
                user_id bigint NOT NULL,
                transfer_id bigint,
                pp_token character varying(30) DEFAULT ''::character varying NOT NULL,
                pp_payer_id character varying(30),
                pp_txn_id character varying(30),
                pp_sum double precision NOT NULL,
                pp_sku character varying(50),
                pp_status smallint DEFAULT 0 NOT NULL,
                pp_status_info character varying(30) DEFAULT ''::character varying NOT NULL,
                pp_created timestamp with time zone NOT NULL,
                pp_updated timestamp with time zone NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}paypal_pays.pp_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}paypal_pays.pp_id IS 'Internal ID';


            --
            -- Name: COLUMN {$tablePrefix}paypal_pays.pp_payment_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}paypal_pays.pp_payment_id IS 'PayPal paymentId';


            --
            -- Name: COLUMN {$tablePrefix}paypal_pays.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}paypal_pays.user_id IS 'Internal user  ID';


            --
            -- Name: COLUMN {$tablePrefix}paypal_pays.transfer_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}paypal_pays.transfer_id IS 'Internal transfer ID';


            --
            -- Name: COLUMN {$tablePrefix}paypal_pays.pp_token; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}paypal_pays.pp_token IS 'PayPal payment create token';


            --
            -- Name: COLUMN {$tablePrefix}paypal_pays.pp_payer_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}paypal_pays.pp_payer_id IS 'PayPal payer ID';


            --
            -- Name: COLUMN {$tablePrefix}paypal_pays.pp_sum; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}paypal_pays.pp_sum IS 'Summ of Payment';


            --
            -- Name: COLUMN {$tablePrefix}paypal_pays.pp_status_info; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}paypal_pays.pp_status_info IS 'Payment Status';


            --
            -- Name: COLUMN {$tablePrefix}paypal_pays.pp_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}paypal_pays.pp_created IS 'Created';


            --
            -- Name: COLUMN {$tablePrefix}paypal_pays.pp_updated; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}paypal_pays.pp_updated IS 'Updated';


            --
            -- Name: {$tablePrefix}paypal_pays_pp_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}paypal_pays_pp_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}paypal_pays_pp_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}paypal_pays_pp_id_seq OWNED BY {$tablePrefix}paypal_pays.pp_id;


            --
            -- Name: {$tablePrefix}pears; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}pears (
                pear_id bigint NOT NULL,
                user_id bigint DEFAULT '0'::bigint NOT NULL,
                pear_name character varying(255) NOT NULL,
                pear_hash character varying(255) NOT NULL,
                pear_status smallint DEFAULT 0 NOT NULL,
                pear_created timestamp with time zone NOT NULL,
                pear_updated timestamp with time zone NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}pears.pear_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pears.pear_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}pears.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pears.user_id IS 'User_Id';


            --
            -- Name: COLUMN {$tablePrefix}pears.pear_name; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pears.pear_name IS 'Имя файла';


            --
            -- Name: COLUMN {$tablePrefix}pears.pear_hash; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pears.pear_hash IS 'Хеш файла';


            --
            -- Name: COLUMN {$tablePrefix}pears.pear_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pears.pear_created IS 'Дата создания';


            --
            -- Name: COLUMN {$tablePrefix}pears.pear_updated; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}pears.pear_updated IS 'Дата изменения';


            --
            -- Name: {$tablePrefix}pears_pear_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}pears_pear_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}pears_pear_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}pears_pear_id_seq OWNED BY {$tablePrefix}pears.pear_id;


            --
            -- Name: {$tablePrefix}preferences; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}preferences (
                pref_id bigint NOT NULL,
                pref_title character varying(255) NOT NULL,
                pref_key character varying(50) NOT NULL,
                pref_value character varying(255) NOT NULL,
                pref_category smallint DEFAULT '1'::smallint NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}preferences.pref_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}preferences.pref_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}preferences.pref_title; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}preferences.pref_title IS 'Title';


            --
            -- Name: COLUMN {$tablePrefix}preferences.pref_key; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}preferences.pref_key IS 'Key';


            --
            -- Name: COLUMN {$tablePrefix}preferences.pref_value; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}preferences.pref_value IS 'Val';


            --
            -- Name: COLUMN {$tablePrefix}preferences.pref_category; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}preferences.pref_category IS 'Category';


            --
            -- Name: {$tablePrefix}preferences_pref_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}preferences_pref_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}preferences_pref_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}preferences_pref_id_seq OWNED BY {$tablePrefix}preferences.pref_id;


            --
            -- Name: {$tablePrefix}remote_actions; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}remote_actions (
                action_id bigint NOT NULL,
                action_uuid character varying(32) DEFAULT ''::character varying NOT NULL,
                action_type {$tablePrefix}remote_actions_action_type DEFAULT 'logout'::{$tablePrefix}remote_actions_action_type NOT NULL,
                source_node_id bigint DEFAULT '0'::bigint NOT NULL,
                target_node_id bigint DEFAULT '0'::bigint NOT NULL,
                user_id bigint DEFAULT '0'::bigint NOT NULL,
                action_init_time timestamp with time zone,
                action_end_time timestamp with time zone
            );


            --
            -- Name: TABLE {$tablePrefix}remote_actions; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON TABLE {$tablePrefix}remote_actions IS 'Таблица logout & wipe';


            --
            -- Name: COLUMN {$tablePrefix}remote_actions.action_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}remote_actions.action_id IS 'ID';


            --
            -- Name: COLUMN {$tablePrefix}remote_actions.action_uuid; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}remote_actions.action_uuid IS 'uuid-операции, unique, not null';


            --
            -- Name: COLUMN {$tablePrefix}remote_actions.action_type; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}remote_actions.action_type IS 'тип операции';


            --
            -- Name: COLUMN {$tablePrefix}remote_actions.source_node_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}remote_actions.source_node_id IS 'Информация об источнике, с которого была инициирована операция, для сайта ноль, для ноды node_id';


            --
            -- Name: COLUMN {$tablePrefix}remote_actions.target_node_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}remote_actions.target_node_id IS 'Идентификатор node_id целевой ноды';


            --
            -- Name: COLUMN {$tablePrefix}remote_actions.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}remote_actions.user_id IS 'Идентификатор user_id для ускорения отбора по пользователю';


            --
            -- Name: COLUMN {$tablePrefix}remote_actions.action_init_time; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}remote_actions.action_init_time IS 'содержит время когда была инициирована операция (время создания записи в БД).';


            --
            -- Name: COLUMN {$tablePrefix}remote_actions.action_end_time; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}remote_actions.action_end_time IS 'содержит время когда операция была завершена, если получен ответ от целевой ноды об успешном окончании операции.';


            --
            -- Name: {$tablePrefix}remote_actions_action_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}remote_actions_action_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}remote_actions_action_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}remote_actions_action_id_seq OWNED BY {$tablePrefix}remote_actions.action_id;


            --
            -- Name: {$tablePrefix}servers; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}servers (
                server_id bigint NOT NULL,
                server_type {$tablePrefix}servers_server_type DEFAULT 'TURN'::{$tablePrefix}servers_server_type NOT NULL,
                server_title character varying(255) NOT NULL,
                server_url character varying(255) DEFAULT ''::character varying NOT NULL,
                server_ip character varying(15) DEFAULT '0.0.0.0'::character varying NOT NULL,
                server_port integer DEFAULT 0 NOT NULL,
                server_login character(50) DEFAULT ''::bpchar NOT NULL,
                server_password character(50) DEFAULT ''::bpchar NOT NULL,
                server_status smallint DEFAULT 1 NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}servers.server_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}servers.server_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}servers.server_type; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}servers.server_type IS 'TURN or STUN or SIGN or PROXY';


            --
            -- Name: COLUMN {$tablePrefix}servers.server_title; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}servers.server_title IS 'Description';


            --
            -- Name: COLUMN {$tablePrefix}servers.server_url; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}servers.server_url IS 'Connect URL';


            --
            -- Name: COLUMN {$tablePrefix}servers.server_ip; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}servers.server_ip IS 'Reserved Server IP';


            --
            -- Name: COLUMN {$tablePrefix}servers.server_port; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}servers.server_port IS 'Reserved Server Port';


            --
            -- Name: COLUMN {$tablePrefix}servers.server_login; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}servers.server_login IS 'Login for access to Server';


            --
            -- Name: COLUMN {$tablePrefix}servers.server_password; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}servers.server_password IS 'Password for access to Server';


            --
            -- Name: {$tablePrefix}servers_server_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}servers_server_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}servers_server_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}servers_server_id_seq OWNED BY {$tablePrefix}servers.server_id;


            --
            -- Name: {$tablePrefix}sessions; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}sessions (
                sess_id bigint NOT NULL,
                sess_countrycode character varying(2) DEFAULT ''::character varying NOT NULL,
                sess_country character varying(40) DEFAULT ''::character varying NOT NULL,
                sess_city character varying(40) DEFAULT ''::character varying NOT NULL,
                sess_useragent character varying(255) DEFAULT ''::character varying NOT NULL,
                sess_ip bigint DEFAULT '0'::bigint NOT NULL,
                sess_action character varying(30) DEFAULT ''::character varying NOT NULL,
                sess_created timestamp with time zone NOT NULL,
                user_id bigint NOT NULL,
                node_id bigint
            );


            --
            -- Name: COLUMN {$tablePrefix}sessions.sess_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}sessions.sess_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}sessions.sess_countrycode; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}sessions.sess_countrycode IS 'Country Code';


            --
            -- Name: COLUMN {$tablePrefix}sessions.sess_country; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}sessions.sess_country IS 'Country Name';


            --
            -- Name: COLUMN {$tablePrefix}sessions.sess_city; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}sessions.sess_city IS 'City Name';


            --
            -- Name: COLUMN {$tablePrefix}sessions.sess_useragent; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}sessions.sess_useragent IS 'UserAgent';


            --
            -- Name: COLUMN {$tablePrefix}sessions.sess_ip; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}sessions.sess_ip IS 'IP Address';


            --
            -- Name: COLUMN {$tablePrefix}sessions.sess_action; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}sessions.sess_action IS 'Действие';


            --
            -- Name: COLUMN {$tablePrefix}sessions.sess_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}sessions.sess_created IS 'Date Time';


            --
            -- Name: COLUMN {$tablePrefix}sessions.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}sessions.user_id IS 'UserId';


            --
            -- Name: COLUMN {$tablePrefix}sessions.node_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}sessions.node_id IS 'Ссылка на таблтцу user_node.node_id';


            --
            -- Name: {$tablePrefix}sessions_sess_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}sessions_sess_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}sessions_sess_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}sessions_sess_id_seq OWNED BY {$tablePrefix}sessions.sess_id;


            --
            -- Name: {$tablePrefix}software; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}software (
                software_id bigint NOT NULL,
                software_type {$tablePrefix}software_software_type DEFAULT 'windows'::{$tablePrefix}software_software_type NOT NULL,
                software_description character varying(255) NOT NULL,
                software_file_name character varying(255) DEFAULT ''::character varying NOT NULL,
                software_url character varying(255) DEFAULT ''::character varying NOT NULL,
                software_program_type {$tablePrefix}software_software_program_type DEFAULT 'file'::{$tablePrefix}software_software_program_type NOT NULL,
                software_version character varying(15) DEFAULT '0.0.0'::character varying NOT NULL,
                software_created timestamp with time zone NOT NULL,
                software_updated timestamp with time zone NOT NULL,
                software_status smallint DEFAULT 0 NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}software.software_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}software.software_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}software.software_type; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}software.software_type IS 'Выберите операционную систему';


            --
            -- Name: COLUMN {$tablePrefix}software.software_description; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}software.software_description IS 'Описание';


            --
            -- Name: COLUMN {$tablePrefix}software.software_file_name; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}software.software_file_name IS 'Путь к файлу';


            --
            -- Name: COLUMN {$tablePrefix}software.software_url; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}software.software_url IS 'Ссылка на приложение';


            --
            -- Name: COLUMN {$tablePrefix}software.software_program_type; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}software.software_program_type IS 'Тип программы (ссылка или файл)';


            --
            -- Name: COLUMN {$tablePrefix}software.software_version; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}software.software_version IS 'Версия программы';


            --
            -- Name: COLUMN {$tablePrefix}software.software_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}software.software_created IS 'Дата создания записи';


            --
            -- Name: COLUMN {$tablePrefix}software.software_updated; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}software.software_updated IS 'Дата обновления записи';


            --
            -- Name: {$tablePrefix}software_software_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}software_software_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}software_software_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}software_software_id_seq OWNED BY {$tablePrefix}software.software_id;


            --
            -- Name: {$tablePrefix}tikets; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}tikets (
                tiket_id bigint NOT NULL,
                tiket_created timestamp with time zone NOT NULL,
                tiket_theme character varying(255) DEFAULT ''::character varying NOT NULL,
                tiket_email character varying(100) DEFAULT ''::character varying NOT NULL,
                tiket_name character varying(100) DEFAULT ''::character varying NOT NULL,
                tiket_count_new_user smallint DEFAULT '0'::smallint NOT NULL,
                tiket_count_new_admin smallint DEFAULT '0'::smallint NOT NULL,
                user_id bigint DEFAULT '0'::bigint NOT NULL,
                admin_id bigint DEFAULT '0'::bigint NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}tikets.tiket_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets.tiket_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}tikets.tiket_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets.tiket_created IS 'date';


            --
            -- Name: COLUMN {$tablePrefix}tikets.tiket_theme; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets.tiket_theme IS 'Theme';


            --
            -- Name: COLUMN {$tablePrefix}tikets.tiket_email; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets.tiket_email IS 'Email';


            --
            -- Name: COLUMN {$tablePrefix}tikets.tiket_name; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets.tiket_name IS 'Name';


            --
            -- Name: COLUMN {$tablePrefix}tikets.tiket_count_new_user; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets.tiket_count_new_user IS 'Count New message for User';


            --
            -- Name: COLUMN {$tablePrefix}tikets.tiket_count_new_admin; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets.tiket_count_new_admin IS 'Count New message for Admin';


            --
            -- Name: COLUMN {$tablePrefix}tikets.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets.user_id IS 'User Id';


            --
            -- Name: COLUMN {$tablePrefix}tikets.admin_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets.admin_id IS 'Admin Id';


            --
            -- Name: {$tablePrefix}tikets_messages; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}tikets_messages (
                message_id bigint NOT NULL,
                message_created timestamp with time zone NOT NULL,
                message_text text,
                message_read_user smallint DEFAULT 0 NOT NULL,
                message_read_admin smallint DEFAULT 0 NOT NULL,
                message_deleted_user smallint DEFAULT 0 NOT NULL,
                message_deleted_admin smallint DEFAULT 0 NOT NULL,
                tiket_id bigint DEFAULT '0'::bigint NOT NULL,
                user_id bigint DEFAULT '0'::bigint NOT NULL,
                admin_id bigint DEFAULT '0'::bigint NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}tikets_messages.message_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets_messages.message_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}tikets_messages.message_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets_messages.message_created IS 'date';


            --
            -- Name: COLUMN {$tablePrefix}tikets_messages.message_text; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets_messages.message_text IS 'Message';


            --
            -- Name: COLUMN {$tablePrefix}tikets_messages.tiket_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets_messages.tiket_id IS 'Tiket Id';


            --
            -- Name: COLUMN {$tablePrefix}tikets_messages.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets_messages.user_id IS 'User Id';


            --
            -- Name: COLUMN {$tablePrefix}tikets_messages.admin_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}tikets_messages.admin_id IS 'Admin Id';


            --
            -- Name: {$tablePrefix}tikets_messages_message_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}tikets_messages_message_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}tikets_messages_message_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}tikets_messages_message_id_seq OWNED BY {$tablePrefix}tikets_messages.message_id;


            --
            -- Name: {$tablePrefix}tikets_tiket_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}tikets_tiket_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}tikets_tiket_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}tikets_tiket_id_seq OWNED BY {$tablePrefix}tikets.tiket_id;


            --
            -- Name: {$tablePrefix}transfers; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}transfers (
                transfer_id bigint NOT NULL,
                user_id bigint NOT NULL,
                transfer_sum double precision NOT NULL,
                transfer_type smallint DEFAULT '0'::smallint NOT NULL,
                transfer_status smallint DEFAULT 0 NOT NULL,
                transfer_created timestamp with time zone NOT NULL,
                transfer_updated timestamp with time zone NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}transfers.transfer_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}transfers.transfer_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}transfers.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}transfers.user_id IS 'user id';


            --
            -- Name: COLUMN {$tablePrefix}transfers.transfer_sum; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}transfers.transfer_sum IS 'Сумма';


            --
            -- Name: COLUMN {$tablePrefix}transfers.transfer_type; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}transfers.transfer_type IS 'Тип платежа';


            --
            -- Name: COLUMN {$tablePrefix}transfers.transfer_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}transfers.transfer_created IS 'Дата создания';


            --
            -- Name: COLUMN {$tablePrefix}transfers.transfer_updated; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}transfers.transfer_updated IS 'Дата обновления';


            --
            -- Name: {$tablePrefix}transfers_transfer_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}transfers_transfer_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}transfers_transfer_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}transfers_transfer_id_seq OWNED BY {$tablePrefix}transfers.transfer_id;


            --
            -- Name: {$tablePrefix}user_collaborations; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}user_collaborations (
                collaboration_id bigint NOT NULL,
                collaboration_status smallint DEFAULT 1 NOT NULL,
                file_uuid character varying(32),
                user_id bigint
            );


            --
            -- Name: TABLE {$tablePrefix}user_collaborations; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON TABLE {$tablePrefix}user_collaborations IS 'Таблица коллаборации';


            --
            -- Name: COLUMN {$tablePrefix}user_collaborations.collaboration_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_collaborations.collaboration_id IS 'ID';


            --
            -- Name: COLUMN {$tablePrefix}user_collaborations.file_uuid; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_collaborations.file_uuid IS 'Ссылка на user_files.file_uuid';


            --
            -- Name: COLUMN {$tablePrefix}user_collaborations.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_collaborations.user_id IS 'ссылка на id пользователя который создал коллаборацию (владелец) users.user_id';


            --
            -- Name: {$tablePrefix}user_collaborations_collaboration_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}user_collaborations_collaboration_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}user_collaborations_collaboration_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}user_collaborations_collaboration_id_seq OWNED BY {$tablePrefix}user_collaborations.collaboration_id;


            --
            -- Name: {$tablePrefix}user_colleagues; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}user_colleagues (
                colleague_id bigint NOT NULL,
                colleague_status {$tablePrefix}user_colleagues_colleague_status DEFAULT 'invited'::{$tablePrefix}user_colleagues_colleague_status NOT NULL,
                colleague_permission {$tablePrefix}user_colleagues_colleague_permission DEFAULT 'view'::{$tablePrefix}user_colleagues_colleague_permission NOT NULL,
                colleague_invite_date timestamp with time zone,
                colleague_joined_date timestamp with time zone,
                colleague_email character varying(50) DEFAULT ''::character varying NOT NULL,
                user_id bigint,
                collaboration_id bigint DEFAULT '0'::bigint NOT NULL
            );


            --
            -- Name: TABLE {$tablePrefix}user_colleagues; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON TABLE {$tablePrefix}user_colleagues IS 'Таблица коллаборации файлов пользователей';


            --
            -- Name: COLUMN {$tablePrefix}user_colleagues.colleague_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_colleagues.colleague_id IS 'ID';


            --
            -- Name: COLUMN {$tablePrefix}user_colleagues.colleague_status; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_colleagues.colleague_status IS 'Статус joined|invited';


            --
            -- Name: COLUMN {$tablePrefix}user_colleagues.colleague_permission; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_colleagues.colleague_permission IS 'Права view|edit|owner';


            --
            -- Name: COLUMN {$tablePrefix}user_colleagues.colleague_invite_date; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_colleagues.colleague_invite_date IS 'Дата приглашения';


            --
            -- Name: COLUMN {$tablePrefix}user_colleagues.colleague_joined_date; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_colleagues.colleague_joined_date IS 'Дата присоединения';


            --
            -- Name: COLUMN {$tablePrefix}user_colleagues.colleague_email; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_colleagues.colleague_email IS 'E-Mail коллаборанта';


            --
            -- Name: COLUMN {$tablePrefix}user_colleagues.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_colleagues.user_id IS 'ссылка на id пользователя users.user_id. Если NULL то пользователь еще не зарегистрирован';


            --
            -- Name: COLUMN {$tablePrefix}user_colleagues.collaboration_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_colleagues.collaboration_id IS 'ссылка на user_collaborations.collaboration_id';


            --
            -- Name: {$tablePrefix}user_colleagues_colleague_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}user_colleagues_colleague_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}user_colleagues_colleague_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}user_colleagues_colleague_id_seq OWNED BY {$tablePrefix}user_colleagues.colleague_id;


            --
            -- Name: {$tablePrefix}user_file_events; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}user_file_events (
                event_id bigint NOT NULL,
                event_uuid character varying(32),
                event_type smallint DEFAULT '0'::smallint NOT NULL,
                event_timestamp bigint DEFAULT '0'::bigint NOT NULL,
                event_invisible smallint DEFAULT 0 NOT NULL,
                last_event_id bigint DEFAULT '0'::bigint NOT NULL,
                diff_file_uuid character varying(32),
                diff_file_size bigint DEFAULT '0'::bigint NOT NULL,
                rev_diff_file_uuid character varying(32),
                rev_diff_file_size bigint DEFAULT '0'::bigint NOT NULL,
                file_id bigint DEFAULT '0'::bigint NOT NULL,
                file_hash_before_event character varying(32),
                file_hash character varying(32),
                file_name_before_event character varying(255) DEFAULT ''::character varying NOT NULL,
                file_name_after_event character varying(255) DEFAULT ''::character varying NOT NULL,
                file_size_before_event bigint DEFAULT '0'::bigint NOT NULL,
                file_size_after_event bigint DEFAULT '0'::bigint NOT NULL,
                node_id bigint DEFAULT '0'::bigint NOT NULL,
                user_id bigint DEFAULT '0'::bigint NOT NULL,
                erase_nested smallint DEFAULT '0'::smallint NOT NULL
            );


            --
            -- Name: TABLE {$tablePrefix}user_file_events; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON TABLE {$tablePrefix}user_file_events IS 'ВНИМАНИЕ !! При вставке в табличку user_file_events должна соблюдаться следующая логика: Если у события полученного от ноды last_event_id is null то в соответсвующей табличке files не должно быть записи с таким же uuid как в этом событии. Если же last_event_id указан, то он должен совпадать с максимальным event_id для этого файла/папки. Если эти условия нарушаются то возникает конфликт синхронизации. Дополнительно предусмотреть проверку, не допускающую регистрацию новых событий по файлу/папке после события delete';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.event_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.event_id IS 'id события';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.event_uuid; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.event_uuid IS 'уникальный идентификатор события которое однозначно определяет состояние файла в момент события';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.event_type; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.event_type IS 'тип события (create = 0, update = 1, delete = 2, move = 3, fork = 4)';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.event_timestamp; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.event_timestamp IS 'отметка времени. Должна выставляться сервером в момент регистрации события';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.last_event_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.last_event_id IS 'ссылка на предыдущее событие. Получаем от ноды. not null. Должно быть 0 для события create и не 0 для всех остальных';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.diff_file_uuid; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.diff_file_uuid IS 'uuid файла с разницей данных, может быть null';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.diff_file_size; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.diff_file_size IS 'размер файла с разницей данных, not null. Может быть равным 0';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.rev_diff_file_uuid; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.rev_diff_file_uuid IS 'rev_uuid файла с разницей данных, может быть null';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.rev_diff_file_size; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.rev_diff_file_size IS 'размер rev-файла с разницей данных, not null. Может быть равным 0';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.file_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.file_id IS 'ссылка на таблицу файлов пользователя user_files.file_id';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.file_hash_before_event; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.file_hash_before_event IS 'md5-hash файла до события';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.file_hash; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.file_hash IS 'контрольная сумма файла после применения этого события. строка из 32-х ASCII символов';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.file_name_before_event; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.file_name_before_event IS 'имя файла до события';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.file_name_after_event; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.file_name_after_event IS 'имя файла после события';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.file_size_before_event; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.file_size_before_event IS 'размер файла до события';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.file_size_after_event; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.file_size_after_event IS 'размер файла после события';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.node_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.node_id IS 'id ноды, на которой возникло событие ссылка на user_node.node_id';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.user_id IS 'ссылка на id пользователя users.user_id.';


            --
            -- Name: COLUMN {$tablePrefix}user_file_events.erase_nested; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_file_events.erase_nested IS 'Признак удаления всех чайлдов при выполнении этого евента';


            --
            -- Name: {$tablePrefix}user_file_events_event_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}user_file_events_event_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}user_file_events_event_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}user_file_events_event_id_seq OWNED BY {$tablePrefix}user_file_events.event_id;


            --
            -- Name: {$tablePrefix}user_files; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}user_files (
                file_id bigint NOT NULL,
                file_parent_id bigint DEFAULT '0'::bigint NOT NULL,
                file_uuid character varying(32) DEFAULT ''::character varying NOT NULL,
                file_name character varying(255) DEFAULT ''::character varying NOT NULL,
                file_size bigint DEFAULT '0'::bigint NOT NULL,
                file_md5 character varying(32) DEFAULT ''::character varying NOT NULL,
                file_created bigint,
                file_updated bigint,
                file_lastatime bigint DEFAULT '0'::bigint NOT NULL,
                is_folder smallint DEFAULT 0 NOT NULL,
                is_deleted smallint DEFAULT 0 NOT NULL,
                is_updated smallint DEFAULT 0 NOT NULL,
                is_outdated bigint DEFAULT '0'::bigint NOT NULL,
                last_event_type smallint DEFAULT '0'::smallint NOT NULL,
                diff_file_uuid character varying(32),
                user_id bigint DEFAULT '0'::bigint NOT NULL,
                node_id bigint DEFAULT '0'::bigint NOT NULL,
                collaboration_id bigint,
                is_collaborated smallint DEFAULT 0 NOT NULL,
                is_owner smallint DEFAULT 1 NOT NULL,
                is_shared smallint DEFAULT 0 NOT NULL,
                share_hash character varying(32),
                share_group_hash character varying(32),
                share_created timestamp with time zone,
                share_lifetime timestamp with time zone,
                share_ttl_info bigint,
                share_password character varying(32)
            );


            --
            -- Name: TABLE {$tablePrefix}user_files; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON TABLE {$tablePrefix}user_files IS 'При нарушении unique_key(name, parent_folder_id, user_id) возникает конфликт имени файла. Размер файла не должен храниться в этой табличке, (как впрочем и в других), т.к. текущая версия API не позволяет серверу его правильно отслеживать';


            --
            -- Name: COLUMN {$tablePrefix}user_files.file_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.file_id IS 'id файла';


            --
            -- Name: COLUMN {$tablePrefix}user_files.file_parent_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.file_parent_id IS 'Родительский id. По сути - ссылка на папку в которой находится файл. можеть быть 0 для файлов лежащих в корневой папке';


            --
            -- Name: COLUMN {$tablePrefix}user_files.file_uuid; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.file_uuid IS '32-х битный идентификатор (например 90d9a178f3b1e725e13735faac1e9315)';


            --
            -- Name: COLUMN {$tablePrefix}user_files.file_name; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.file_name IS 'имя файла';


            --
            -- Name: COLUMN {$tablePrefix}user_files.file_size; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.file_size IS 'Размер файла';


            --
            -- Name: COLUMN {$tablePrefix}user_files.file_md5; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.file_md5 IS 'Контрольная сумма md5-файла';


            --
            -- Name: COLUMN {$tablePrefix}user_files.file_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.file_created IS 'Таймстамп создания файла';


            --
            -- Name: COLUMN {$tablePrefix}user_files.file_updated; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.file_updated IS 'Таймстамп обновления файла';


            --
            -- Name: COLUMN {$tablePrefix}user_files.file_lastatime; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.file_lastatime IS 'Last access time';


            --
            -- Name: COLUMN {$tablePrefix}user_files.is_outdated; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.is_outdated IS 'Признак того что файл удален и уже вне даты восстановления и он уже обработан кроном';


            --
            -- Name: COLUMN {$tablePrefix}user_files.last_event_type; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.last_event_type IS 'Последнее событие, зарегистрированное по этому файлу';


            --
            -- Name: COLUMN {$tablePrefix}user_files.diff_file_uuid; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.diff_file_uuid IS 'uuid файла с разницей данных - последняя версия этого uuid (из таблицы user_file_events. создано в этой таблице для уменьшения нагрузки при запросах)';


            --
            -- Name: COLUMN {$tablePrefix}user_files.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.user_id IS 'ссылка на id пользователя users.user_id';


            --
            -- Name: COLUMN {$tablePrefix}user_files.node_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.node_id IS 'Идетификатор ноды';


            --
            -- Name: COLUMN {$tablePrefix}user_files.collaboration_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.collaboration_id IS 'Ссылка на user_collaborations.collaboration_id';


            --
            -- Name: COLUMN {$tablePrefix}user_files.share_hash; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.share_hash IS 'уникальный идентификатор расшаренного файла или папки. Может быть NULL (нет шаринга)';


            --
            -- Name: COLUMN {$tablePrefix}user_files.share_group_hash; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.share_group_hash IS 'идентификатор группы расшаренных файлов и папок (при расшаривании папки). Может быть NULL (нет группового шаринга)';


            --
            -- Name: COLUMN {$tablePrefix}user_files.share_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.share_created IS 'Дата создания шаринга';


            --
            -- Name: COLUMN {$tablePrefix}user_files.share_lifetime; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.share_lifetime IS 'Дата окончания шаринга';


            --
            -- Name: COLUMN {$tablePrefix}user_files.share_ttl_info; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.share_ttl_info IS 'Информационное поле для получения ТТЛ шары и вывода его в селект поле';


            --
            -- Name: COLUMN {$tablePrefix}user_files.share_password; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_files.share_password IS 'Пароль доступа на расшаренный элемент, если NULL - нет пароля';


            --
            -- Name: {$tablePrefix}user_files_file_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}user_files_file_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}user_files_file_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}user_files_file_id_seq OWNED BY {$tablePrefix}user_files.file_id;


            --
            -- Name: {$tablePrefix}user_node; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}user_node (
                node_id bigint NOT NULL,
                node_hash character varying(128) NOT NULL,
                node_name character varying(30),
                node_created timestamp with time zone NOT NULL,
                node_updated timestamp with time zone NOT NULL,
                node_last_ip bigint DEFAULT '0'::bigint NOT NULL,
                node_countrycode character varying(2) DEFAULT ''::character varying NOT NULL,
                node_country character varying(40) DEFAULT ''::character varying NOT NULL,
                node_city character varying(40) DEFAULT ''::character varying NOT NULL,
                node_useragent character varying(255) DEFAULT ''::character varying NOT NULL,
                node_osname character varying(255) DEFAULT ''::character varying NOT NULL,
                node_ostype {$tablePrefix}user_node_node_ostype DEFAULT 'Windows'::{$tablePrefix}user_node_node_ostype NOT NULL,
                node_devicetype {$tablePrefix}user_node_node_devicetype DEFAULT 'desktop'::{$tablePrefix}user_node_node_devicetype NOT NULL,
                node_online smallint DEFAULT 1 NOT NULL,
                node_status smallint DEFAULT 1 NOT NULL,
                node_upload_speed bigint DEFAULT '0'::bigint NOT NULL,
                node_download_speed bigint DEFAULT '0'::bigint NOT NULL,
                node_disk_usage bigint DEFAULT '0'::bigint NOT NULL,
                node_logout_status smallint DEFAULT 0 NOT NULL,
                node_wipe_status smallint DEFAULT 0 NOT NULL,
                user_id bigint NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_hash; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_hash IS 'NodeHash';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_created IS 'Дата регистранции устройства';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_updated; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_updated IS 'Дата последней активности';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_last_ip; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_last_ip IS 'ИП устройства';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_countrycode; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_countrycode IS 'Код страны';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_country; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_country IS 'Страна';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_city; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_city IS 'Город';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_useragent; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_useragent IS 'UserAgent устройства';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_osname; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_osname IS 'Имя ОС';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_ostype; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_ostype IS 'Тип ОС';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_devicetype; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_devicetype IS 'Тип устройства';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_upload_speed; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_upload_speed IS 'Скорость передачи данных';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_download_speed; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_download_speed IS 'Скорость получения данных';


            --
            -- Name: COLUMN {$tablePrefix}user_node.node_disk_usage; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.node_disk_usage IS 'Количество байт, занятых файлами';


            --
            -- Name: COLUMN {$tablePrefix}user_node.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_node.user_id IS 'UserId';


            --
            -- Name: {$tablePrefix}user_node_node_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}user_node_node_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}user_node_node_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}user_node_node_id_seq OWNED BY {$tablePrefix}user_node.node_id;


            --
            -- Name: {$tablePrefix}user_uploads; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}user_uploads (
                upload_id bigint NOT NULL,
                upload_md5 character varying(32) DEFAULT ''::character varying NOT NULL,
                upload_path character varying(255) DEFAULT ''::character varying NOT NULL,
                upload_size bigint DEFAULT '0'::bigint NOT NULL,
                file_parent_id bigint DEFAULT '0'::bigint NOT NULL,
                user_id bigint DEFAULT '0'::bigint NOT NULL,
                node_id bigint DEFAULT '0'::bigint NOT NULL
            );


            --
            -- Name: TABLE {$tablePrefix}user_uploads; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON TABLE {$tablePrefix}user_uploads IS 'Размер загруженного файла';


            --
            -- Name: COLUMN {$tablePrefix}user_uploads.upload_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_uploads.upload_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}user_uploads.upload_md5; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_uploads.upload_md5 IS 'MD5';


            --
            -- Name: COLUMN {$tablePrefix}user_uploads.upload_path; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_uploads.upload_path IS 'File Name';


            --
            -- Name: COLUMN {$tablePrefix}user_uploads.file_parent_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_uploads.file_parent_id IS 'Родительский id. По сути - ссылка на папку в которой находится файл. можеть быть 0 для файлов лежащих в корневой папке';


            --
            -- Name: COLUMN {$tablePrefix}user_uploads.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_uploads.user_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}user_uploads.node_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}user_uploads.node_id IS 'Id';


            --
            -- Name: {$tablePrefix}user_uploads_upload_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}user_uploads_upload_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}user_uploads_upload_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}user_uploads_upload_id_seq OWNED BY {$tablePrefix}user_uploads.upload_id;


            --
            -- Name: {$tablePrefix}users; Type: TABLE; Schema: {$schema}; Owner: -
            --

            CREATE TABLE {$tablePrefix}users (
                user_id bigint NOT NULL,
                user_name character varying(50) NOT NULL,
                user_email character varying(50) NOT NULL,
                user_hash character varying(50) DEFAULT ''::character varying NOT NULL,
                user_remote_hash character varying(128) DEFAULT ''::character varying NOT NULL,
                auth_key character varying(32) NOT NULL,
                password_hash character varying(255) NOT NULL,
                password_reset_token character varying(255),
                user_status smallint DEFAULT 0 NOT NULL,
                user_balance numeric(11,2) DEFAULT 0.00 NOT NULL,
                user_last_ip bigint DEFAULT '0'::bigint NOT NULL,
                user_created timestamp with time zone NOT NULL,
                user_updated timestamp with time zone NOT NULL,
                user_ref_id bigint DEFAULT '0'::bigint NOT NULL,
                user_closed_confirm smallint DEFAULT 0 NOT NULL,
                license_type character varying(20) DEFAULT 'FREE_DEFAULT'::character varying NOT NULL,
                license_bytes_allowed bigint DEFAULT '0'::bigint NOT NULL,
                license_bytes_sent bigint DEFAULT '0'::bigint NOT NULL
            );


            --
            -- Name: COLUMN {$tablePrefix}users.user_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.user_id IS 'Id';


            --
            -- Name: COLUMN {$tablePrefix}users.user_name; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.user_name IS 'Name';


            --
            -- Name: COLUMN {$tablePrefix}users.user_email; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.user_email IS 'E-mail';


            --
            -- Name: COLUMN {$tablePrefix}users.user_hash; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.user_hash IS 'UserHash';


            --
            -- Name: COLUMN {$tablePrefix}users.user_remote_hash; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.user_remote_hash IS 'UserRemoteHash';


            --
            -- Name: COLUMN {$tablePrefix}users.user_balance; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.user_balance IS 'Ballance';


            --
            -- Name: COLUMN {$tablePrefix}users.user_created; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.user_created IS 'Дата создания';


            --
            -- Name: COLUMN {$tablePrefix}users.user_updated; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.user_updated IS 'Дата изменения';


            --
            -- Name: COLUMN {$tablePrefix}users.user_ref_id; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.user_ref_id IS 'Referal ID';


            --
            -- Name: COLUMN {$tablePrefix}users.license_type; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.license_type IS 'Тип лицензии';


            --
            -- Name: COLUMN {$tablePrefix}users.license_bytes_allowed; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.license_bytes_allowed IS 'Количество разрешенных байт по бесплатной лицензии';


            --
            -- Name: COLUMN {$tablePrefix}users.license_bytes_sent; Type: COMMENT; Schema: {$schema}; Owner: -
            --

            COMMENT ON COLUMN {$tablePrefix}users.license_bytes_sent IS 'Количество отправленных байт по бесплатной лицензии';


            --
            -- Name: {$tablePrefix}users_user_id_seq; Type: SEQUENCE; Schema: {$schema}; Owner: -
            --

            CREATE SEQUENCE {$tablePrefix}users_user_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;


            --
            -- Name: {$tablePrefix}users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: {$schema}; Owner: -
            --

            ALTER SEQUENCE {$tablePrefix}users_user_id_seq OWNED BY {$tablePrefix}users.user_id;


            --
            -- Name: {$tablePrefix}admins admin_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}admins ALTER COLUMN admin_id SET DEFAULT nextval('{$tablePrefix}admins_admin_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}colleagues_reports report_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}colleagues_reports ALTER COLUMN report_id SET DEFAULT nextval('{$tablePrefix}colleagues_reports_report_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}licenses license_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}licenses ALTER COLUMN license_id SET DEFAULT nextval('{$tablePrefix}licenses_license_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}mail_templates template_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}mail_templates ALTER COLUMN template_id SET DEFAULT nextval('{$tablePrefix}mail_templates_template_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}news news_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}news ALTER COLUMN news_id SET DEFAULT nextval('{$tablePrefix}news_news_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}node_changes ncg_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}node_changes ALTER COLUMN ncg_id SET DEFAULT nextval('{$tablePrefix}node_changes_ncg_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}notifications notif_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}notifications ALTER COLUMN notif_id SET DEFAULT nextval('{$tablePrefix}notifications_notif_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}pages page_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}pages ALTER COLUMN page_id SET DEFAULT nextval('{$tablePrefix}pages_page_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}paypal_pays pp_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}paypal_pays ALTER COLUMN pp_id SET DEFAULT nextval('{$tablePrefix}paypal_pays_pp_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}pears pear_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}pears ALTER COLUMN pear_id SET DEFAULT nextval('{$tablePrefix}pears_pear_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}preferences pref_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}preferences ALTER COLUMN pref_id SET DEFAULT nextval('{$tablePrefix}preferences_pref_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}remote_actions action_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}remote_actions ALTER COLUMN action_id SET DEFAULT nextval('{$tablePrefix}remote_actions_action_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}servers server_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}servers ALTER COLUMN server_id SET DEFAULT nextval('{$tablePrefix}servers_server_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}sessions sess_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}sessions ALTER COLUMN sess_id SET DEFAULT nextval('{$tablePrefix}sessions_sess_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}software software_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}software ALTER COLUMN software_id SET DEFAULT nextval('{$tablePrefix}software_software_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}tikets tiket_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}tikets ALTER COLUMN tiket_id SET DEFAULT nextval('{$tablePrefix}tikets_tiket_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}tikets_messages message_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}tikets_messages ALTER COLUMN message_id SET DEFAULT nextval('{$tablePrefix}tikets_messages_message_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}transfers transfer_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}transfers ALTER COLUMN transfer_id SET DEFAULT nextval('{$tablePrefix}transfers_transfer_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}user_collaborations collaboration_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_collaborations ALTER COLUMN collaboration_id SET DEFAULT nextval('{$tablePrefix}user_collaborations_collaboration_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}user_colleagues colleague_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_colleagues ALTER COLUMN colleague_id SET DEFAULT nextval('{$tablePrefix}user_colleagues_colleague_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}user_file_events event_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_file_events ALTER COLUMN event_id SET DEFAULT nextval('{$tablePrefix}user_file_events_event_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}user_files file_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_files ALTER COLUMN file_id SET DEFAULT nextval('{$tablePrefix}user_files_file_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}user_node node_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_node ALTER COLUMN node_id SET DEFAULT nextval('{$tablePrefix}user_node_node_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}user_uploads upload_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_uploads ALTER COLUMN upload_id SET DEFAULT nextval('{$tablePrefix}user_uploads_upload_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}users user_id; Type: DEFAULT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}users ALTER COLUMN user_id SET DEFAULT nextval('{$tablePrefix}users_user_id_seq'::regclass);


            --
            -- Name: {$tablePrefix}admins idx_17253_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}admins
                ADD CONSTRAINT idx_17253_primary PRIMARY KEY (admin_id);


            --
            -- Name: {$tablePrefix}colleagues_reports idx_17262_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}colleagues_reports
                ADD CONSTRAINT idx_17262_primary PRIMARY KEY (report_id);


            --
            -- Name: {$tablePrefix}licenses idx_17283_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}licenses
                ADD CONSTRAINT idx_17283_primary PRIMARY KEY (license_id);


            --
            -- Name: {$tablePrefix}mail_templates idx_17292_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}mail_templates
                ADD CONSTRAINT idx_17292_primary PRIMARY KEY (template_id);


            --
            -- Name: {$tablePrefix}news idx_17309_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}news
                ADD CONSTRAINT idx_17309_primary PRIMARY KEY (news_id);


            --
            -- Name: {$tablePrefix}node_changes idx_17318_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}node_changes
                ADD CONSTRAINT idx_17318_primary PRIMARY KEY (ncg_id);


            --
            -- Name: {$tablePrefix}notifications idx_17329_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}notifications
                ADD CONSTRAINT idx_17329_primary PRIMARY KEY (notif_id);


            --
            -- Name: {$tablePrefix}pages idx_17337_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}pages
                ADD CONSTRAINT idx_17337_primary PRIMARY KEY (page_id);


            --
            -- Name: {$tablePrefix}paypal_pays idx_17352_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}paypal_pays
                ADD CONSTRAINT idx_17352_primary PRIMARY KEY (pp_id);


            --
            -- Name: {$tablePrefix}pears idx_17361_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}pears
                ADD CONSTRAINT idx_17361_primary PRIMARY KEY (pear_id);


            --
            -- Name: {$tablePrefix}preferences idx_17371_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}preferences
                ADD CONSTRAINT idx_17371_primary PRIMARY KEY (pref_id);


            --
            -- Name: {$tablePrefix}remote_actions idx_17381_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}remote_actions
                ADD CONSTRAINT idx_17381_primary PRIMARY KEY (action_id);


            --
            -- Name: {$tablePrefix}servers idx_17392_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}servers
                ADD CONSTRAINT idx_17392_primary PRIMARY KEY (server_id);


            --
            -- Name: {$tablePrefix}sessions idx_17407_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}sessions
                ADD CONSTRAINT idx_17407_primary PRIMARY KEY (sess_id);


            --
            -- Name: {$tablePrefix}software idx_17419_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}software
                ADD CONSTRAINT idx_17419_primary PRIMARY KEY (software_id);


            --
            -- Name: {$tablePrefix}tikets idx_17433_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}tikets
                ADD CONSTRAINT idx_17433_primary PRIMARY KEY (tiket_id);


            --
            -- Name: {$tablePrefix}tikets_messages idx_17446_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}tikets_messages
                ADD CONSTRAINT idx_17446_primary PRIMARY KEY (message_id);


            --
            -- Name: {$tablePrefix}transfers idx_17458_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}transfers
                ADD CONSTRAINT idx_17458_primary PRIMARY KEY (transfer_id);


            --
            -- Name: {$tablePrefix}users idx_17465_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}users
                ADD CONSTRAINT idx_17465_primary PRIMARY KEY (user_id);


            --
            -- Name: {$tablePrefix}user_collaborations idx_17482_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_collaborations
                ADD CONSTRAINT idx_17482_primary PRIMARY KEY (collaboration_id);


            --
            -- Name: {$tablePrefix}user_colleagues idx_17488_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_colleagues
                ADD CONSTRAINT idx_17488_primary PRIMARY KEY (colleague_id);


            --
            -- Name: {$tablePrefix}user_files idx_17498_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_files
                ADD CONSTRAINT idx_17498_primary PRIMARY KEY (file_id);


            --
            -- Name: {$tablePrefix}user_file_events idx_17514_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_file_events
                ADD CONSTRAINT idx_17514_primary PRIMARY KEY (event_id);


            --
            -- Name: {$tablePrefix}user_node idx_17535_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_node
                ADD CONSTRAINT idx_17535_primary PRIMARY KEY (node_id);


            --
            -- Name: {$tablePrefix}user_uploads idx_17555_primary; Type: CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_uploads
                ADD CONSTRAINT idx_17555_primary PRIMARY KEY (upload_id);


            --
            -- Name: idx_17253_admin_email; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17253_admin_email ON {$tablePrefix}admins USING btree (admin_email);


            --
            -- Name: idx_17253_admin_name; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17253_admin_name ON {$tablePrefix}admins USING btree (admin_name);


            --
            -- Name: idx_17253_password_reset_token; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17253_password_reset_token ON {$tablePrefix}admins USING btree (password_reset_token);


            --
            -- Name: idx_17262_file_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17262_file_id ON {$tablePrefix}colleagues_reports USING btree (file_id);


            --
            -- Name: idx_17262_fk_colleagues_reports_colleague_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17262_fk_colleagues_reports_colleague_user_id ON {$tablePrefix}colleagues_reports USING btree (colleague_user_id);


            --
            -- Name: idx_17262_owner_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17262_owner_user_id ON {$tablePrefix}colleagues_reports USING btree (owner_user_id);


            --
            -- Name: idx_17283_license_type; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17283_license_type ON {$tablePrefix}licenses USING btree (license_type);


            --
            -- Name: idx_17292_template_key_idx; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17292_template_key_idx ON {$tablePrefix}mail_templates USING btree (template_key, template_lang);


            --
            -- Name: idx_17309_news_status; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17309_news_status ON {$tablePrefix}news USING btree (news_status);


            --
            -- Name: idx_17318_ncg_patch; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17318_ncg_patch ON {$tablePrefix}node_changes USING btree (node_hash, ncg_patch);


            --
            -- Name: idx_17318_ncg_patch_uid; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17318_ncg_patch_uid ON {$tablePrefix}node_changes USING btree (user_id, ncg_patch);


            --
            -- Name: idx_17318_ncg_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17318_ncg_user_id ON {$tablePrefix}node_changes USING btree (user_id, ncg_sended);


            --
            -- Name: idx_17318_node_hash; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17318_node_hash ON {$tablePrefix}node_changes USING btree (node_hash, ncg_sended);


            --
            -- Name: idx_17329_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17329_user_id ON {$tablePrefix}notifications USING btree (user_id);


            --
            -- Name: idx_17337_page_alias; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17337_page_alias ON {$tablePrefix}pages USING btree (page_alias, page_lang);


            --
            -- Name: idx_17352_pp_payerid; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17352_pp_payerid ON {$tablePrefix}paypal_pays USING btree (pp_payer_id);


            --
            -- Name: idx_17352_pp_paymentid; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17352_pp_paymentid ON {$tablePrefix}paypal_pays USING btree (pp_payment_id);


            --
            -- Name: idx_17352_pp_sku; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17352_pp_sku ON {$tablePrefix}paypal_pays USING btree (pp_sku);


            --
            -- Name: idx_17352_pp_token; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17352_pp_token ON {$tablePrefix}paypal_pays USING btree (pp_token);


            --
            -- Name: idx_17352_pp_txn_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17352_pp_txn_id ON {$tablePrefix}paypal_pays USING btree (pp_txn_id);


            --
            -- Name: idx_17352_transfer_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17352_transfer_id ON {$tablePrefix}paypal_pays USING btree (transfer_id);


            --
            -- Name: idx_17352_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17352_user_id ON {$tablePrefix}paypal_pays USING btree (user_id);


            --
            -- Name: idx_17361_pear_name; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17361_pear_name ON {$tablePrefix}pears USING btree (pear_name);


            --
            -- Name: idx_17361_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17361_user_id ON {$tablePrefix}pears USING btree (user_id);


            --
            -- Name: idx_17371_pref_category; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17371_pref_category ON {$tablePrefix}preferences USING btree (pref_category);


            --
            -- Name: idx_17371_pref_key; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17371_pref_key ON {$tablePrefix}preferences USING btree (pref_key);


            --
            -- Name: idx_17381_action_uuid; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17381_action_uuid ON {$tablePrefix}remote_actions USING btree (action_uuid);


            --
            -- Name: idx_17381_target_node_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17381_target_node_id ON {$tablePrefix}remote_actions USING btree (target_node_id);


            --
            -- Name: idx_17381_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17381_user_id ON {$tablePrefix}remote_actions USING btree (user_id);


            --
            -- Name: idx_17392_server_isactive; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17392_server_isactive ON {$tablePrefix}servers USING btree (server_status);


            --
            -- Name: idx_17392_server_type; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17392_server_type ON {$tablePrefix}servers USING btree (server_type);


            --
            -- Name: idx_17392_server_url; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17392_server_url ON {$tablePrefix}servers USING btree (server_url);


            --
            -- Name: idx_17407_node_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17407_node_id ON {$tablePrefix}sessions USING btree (node_id);


            --
            -- Name: idx_17407_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17407_user_id ON {$tablePrefix}sessions USING btree (user_id);


            --
            -- Name: idx_17419_software_status; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17419_software_status ON {$tablePrefix}software USING btree (software_status);


            --
            -- Name: idx_17419_software_version; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17419_software_version ON {$tablePrefix}software USING btree (software_type, software_version);


            --
            -- Name: idx_17433_email_idx; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17433_email_idx ON {$tablePrefix}tikets USING btree (tiket_email, user_id);


            --
            -- Name: idx_17433_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17433_user_id ON {$tablePrefix}tikets USING btree (user_id);


            --
            -- Name: idx_17446_tk_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17446_tk_id ON {$tablePrefix}tikets_messages USING btree (tiket_id);


            --
            -- Name: idx_17446_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17446_user_id ON {$tablePrefix}tikets_messages USING btree (user_id);


            --
            -- Name: idx_17458_transfer_options; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17458_transfer_options ON {$tablePrefix}transfers USING btree (transfer_status, transfer_type);


            --
            -- Name: idx_17458_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17458_user_id ON {$tablePrefix}transfers USING btree (user_id);


            --
            -- Name: idx_17465_fk_user_license_type; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17465_fk_user_license_type ON {$tablePrefix}users USING btree (license_type);


            --
            -- Name: idx_17465_password_reset_token; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17465_password_reset_token ON {$tablePrefix}users USING btree (password_reset_token);


            --
            -- Name: idx_17465_user_email; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17465_user_email ON {$tablePrefix}users USING btree (user_email);


            --
            -- Name: idx_17465_user_hash; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17465_user_hash ON {$tablePrefix}users USING btree (user_hash);


            --
            -- Name: idx_17465_user_ref_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17465_user_ref_id ON {$tablePrefix}users USING btree (user_ref_id);


            --
            -- Name: idx_17465_user_remote_hash; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17465_user_remote_hash ON {$tablePrefix}users USING btree (user_remote_hash);


            --
            -- Name: idx_17482_file_uuid; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17482_file_uuid ON {$tablePrefix}user_collaborations USING btree (file_uuid);


            --
            -- Name: idx_17482_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17482_user_id ON {$tablePrefix}user_collaborations USING btree (user_id);


            --
            -- Name: idx_17488_colleague_email; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17488_colleague_email ON {$tablePrefix}user_colleagues USING btree (colleague_email, collaboration_id);


            --
            -- Name: idx_17488_fk_user_colleagues_collaboration_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17488_fk_user_colleagues_collaboration_id ON {$tablePrefix}user_colleagues USING btree (collaboration_id);


            --
            -- Name: idx_17488_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17488_user_id ON {$tablePrefix}user_colleagues USING btree (user_id, collaboration_id);


            --
            -- Name: idx_17498_collaboration_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17498_collaboration_id ON {$tablePrefix}user_files USING btree (collaboration_id, user_id);


            --
            -- Name: idx_17498_file_name; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17498_file_name ON {$tablePrefix}user_files USING btree (file_name, file_parent_id, user_id, is_deleted);


            --
            -- Name: idx_17498_file_parent_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17498_file_parent_id ON {$tablePrefix}user_files USING btree (file_parent_id);


            --
            -- Name: idx_17498_file_uuid; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17498_file_uuid ON {$tablePrefix}user_files USING btree (file_uuid, user_id, is_deleted, collaboration_id);


            --
            -- Name: idx_17498_fk_user_files_user_id2; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17498_fk_user_files_user_id2 ON {$tablePrefix}user_files USING btree (user_id);


            --
            -- Name: idx_17498_is_collaborated; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17498_is_collaborated ON {$tablePrefix}user_files USING btree (is_collaborated, user_id);


            --
            -- Name: idx_17498_is_shared; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17498_is_shared ON {$tablePrefix}user_files USING btree (is_shared, user_id);


            --
            -- Name: idx_17498_last_event_type; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17498_last_event_type ON {$tablePrefix}user_files USING btree (last_event_type, user_id, is_outdated);


            --
            -- Name: idx_17498_share_group_hash; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17498_share_group_hash ON {$tablePrefix}user_files USING btree (share_group_hash);


            --
            -- Name: idx_17498_share_hash; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17498_share_hash ON {$tablePrefix}user_files USING btree (share_hash);


            --
            -- Name: idx_17514_diff_file_uuid; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17514_diff_file_uuid ON {$tablePrefix}user_file_events USING btree (diff_file_uuid);


            --
            -- Name: idx_17514_event_uuid; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17514_event_uuid ON {$tablePrefix}user_file_events USING btree (event_uuid, user_id);


            --
            -- Name: idx_17514_fk_user_file_events_node_id2; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17514_fk_user_file_events_node_id2 ON {$tablePrefix}user_file_events USING btree (node_id);


            --
            -- Name: idx_17514_last_event_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17514_last_event_id ON {$tablePrefix}user_file_events USING btree (file_id, last_event_id);


            --
            -- Name: idx_17514_rev_diff_file_uuid; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17514_rev_diff_file_uuid ON {$tablePrefix}user_file_events USING btree (rev_diff_file_uuid);


            --
            -- Name: idx_17514_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17514_user_id ON {$tablePrefix}user_file_events USING btree (user_id);


            --
            -- Name: idx_17535_node_hash_idx; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17535_node_hash_idx ON {$tablePrefix}user_node USING btree (node_hash);


            --
            -- Name: idx_17535_user_id_idx; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17535_user_id_idx ON {$tablePrefix}user_node USING btree (user_id);


            --
            -- Name: idx_17555_file_parent_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17555_file_parent_id ON {$tablePrefix}user_uploads USING btree (file_parent_id);


            --
            -- Name: idx_17555_fk_user_upload_user_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17555_fk_user_upload_user_id ON {$tablePrefix}user_uploads USING btree (user_id);


            --
            -- Name: idx_17555_node_id; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE INDEX idx_17555_node_id ON {$tablePrefix}user_uploads USING btree (node_id);


            --
            -- Name: idx_17555_upload_path; Type: INDEX; Schema: {$schema}; Owner: -
            --

            CREATE UNIQUE INDEX idx_17555_upload_path ON {$tablePrefix}user_uploads USING btree (upload_path, user_id);


            --
            -- Name: {$tablePrefix}colleagues_reports fk_colleagues_reports_colleague_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}colleagues_reports
                ADD CONSTRAINT fk_colleagues_reports_colleague_user_id FOREIGN KEY (colleague_user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}colleagues_reports fk_colleagues_reports_file_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}colleagues_reports
                ADD CONSTRAINT fk_colleagues_reports_file_id FOREIGN KEY (file_id) REFERENCES {$tablePrefix}user_files(file_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}colleagues_reports fk_colleagues_reports_owner_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}colleagues_reports
                ADD CONSTRAINT fk_colleagues_reports_owner_user_id FOREIGN KEY (owner_user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}node_changes fk_node_changes_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}node_changes
                ADD CONSTRAINT fk_node_changes_user_id FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}notifications fk_notifications_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}notifications
                ADD CONSTRAINT fk_notifications_user_id FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}paypal_pays fk_paypal_transfer_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}paypal_pays
                ADD CONSTRAINT fk_paypal_transfer_id FOREIGN KEY (transfer_id) REFERENCES {$tablePrefix}transfers(transfer_id) ON UPDATE CASCADE ON DELETE SET NULL;


            --
            -- Name: {$tablePrefix}paypal_pays fk_paypal_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}paypal_pays
                ADD CONSTRAINT fk_paypal_user_id FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}remote_actions fk_remote_actions_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}remote_actions
                ADD CONSTRAINT fk_remote_actions_user_id FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}sessions fk_sessions_node_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}sessions
                ADD CONSTRAINT fk_sessions_node_id FOREIGN KEY (node_id) REFERENCES {$tablePrefix}user_node(node_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}sessions fk_sessions_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}sessions
                ADD CONSTRAINT fk_sessions_user_id FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}tikets_messages fk_tikets_tk_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}tikets_messages
                ADD CONSTRAINT fk_tikets_tk_id FOREIGN KEY (tiket_id) REFERENCES {$tablePrefix}tikets(tiket_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}transfers fk_transfers_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}transfers
                ADD CONSTRAINT fk_transfers_user_id FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}user_collaborations fk_user_collaborations_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_collaborations
                ADD CONSTRAINT fk_user_collaborations_user_id FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}user_colleagues fk_user_colleagues_collaboration_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_colleagues
                ADD CONSTRAINT fk_user_colleagues_collaboration_id FOREIGN KEY (collaboration_id) REFERENCES {$tablePrefix}user_collaborations(collaboration_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}user_colleagues fk_user_colleagues_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_colleagues
                ADD CONSTRAINT fk_user_colleagues_user_id FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}user_file_events fk_user_file_events_file_id2; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_file_events
                ADD CONSTRAINT fk_user_file_events_file_id2 FOREIGN KEY (file_id) REFERENCES {$tablePrefix}user_files(file_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}user_file_events fk_user_file_events_node_id2; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_file_events
                ADD CONSTRAINT fk_user_file_events_node_id2 FOREIGN KEY (node_id) REFERENCES {$tablePrefix}user_node(node_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}user_files fk_user_files_collaboration_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_files
                ADD CONSTRAINT fk_user_files_collaboration_id FOREIGN KEY (collaboration_id) REFERENCES {$tablePrefix}user_collaborations(collaboration_id) ON UPDATE CASCADE ON DELETE SET NULL;


            --
            -- Name: {$tablePrefix}user_files fk_user_files_user_id2; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_files
                ADD CONSTRAINT fk_user_files_user_id2 FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}users fk_user_license_type; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}users
                ADD CONSTRAINT fk_user_license_type FOREIGN KEY (license_type) REFERENCES {$tablePrefix}licenses(license_type) ON UPDATE CASCADE;


            --
            -- Name: {$tablePrefix}user_uploads fk_user_upload_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_uploads
                ADD CONSTRAINT fk_user_upload_user_id FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- Name: {$tablePrefix}user_node fk_usernode_user_id; Type: FK CONSTRAINT; Schema: {$schema}; Owner: -
            --

            ALTER TABLE ONLY {$tablePrefix}user_node
                ADD CONSTRAINT fk_usernode_user_id FOREIGN KEY (user_id) REFERENCES {$tablePrefix}users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


            --
            -- PostgreSQL database dump complete
            --
        ");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180115_114152_init_schema_postgre cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180115_114152_init_schema_postgre cannot be reverted.\n";

        return false;
    }
    */
}
