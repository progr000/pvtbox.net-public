<?php
return [
    'title'                     => "Admin panel",
    /* index */
    'Company_name'              => "Company name",
    'Collaboration_settings'    => "Collaboration settings",
    'Reports'                   => "Reports",
    'Server_licenses'           => "Server licenses",

    /* CompanyName */
    'Change_company_name'       => "Change company name",
    'Enter_new_Company_name'    => "Enter new Company's name",
    'Save'                      => "Save",

    /* Reports */
    'Reports_User'              => "User",
    'Reports_Activity'          => "Activity",
    'Reports_Status'            => "Status",
    'Reports_Date'              => "Date",

    /* CollaborationSettings */
    'Invite_colleague'          => "Invite colleague",
    'Invite'                    => "Invite",
    'To_add_colleague_enter'    => "* To add colleague enter colleague e-mail and click \"Add\". Then you will be able to set permission rights to certain user and invitational e-mail will be sent to user.",
    'Colleagues_list'           => "Colleagues list",
    'Count_of_Total_lic_used'   => "<b>{count}</b> of <b>{total}</b> license used.",
    'Count_of_Total_server_lic_used'   => "<b id=\"used-server-license-count\">{count}</b> of <b id=\"total-server-license-count\">{total}</b> server license used.",
    'Add_more'                  => "Add more",
    'CollSet_User'              => "User",
    'CollSet_Status'            => "Status",
    'CollSet_Permission'        => "Permission",
    'CollSet_Action'            => "Action",

    'awaiting_permissions'      => "Awaiting for permissions setup",
    'Remove'                    => "Remove License",
    'external_license'          => "(external license)",
    'Exclude_user'              => "Exclude user",

    'Are_you_sure_to_remove_colleague' => "User's access to collaboration folders will be removed.",

    'list_owner'                => " (owner)",
    'select_filter'             => "All",
    'clear_filter'              => "All",
    'Added_new'                 => "Added new",
    'Restored'                  => "Restored",
    'Modified_or_Restore_patch' => "Modified or Restore patch",
    'Removed'                   => "Removed",
    'Moved_or_Renamed'          => "Moved or Renamed",

    'You_are_the_Admin'         => "You are the Admin",
    'All'                       => "All",
    'Set'                       => "Set",

    'folder'                                => "folder",
    'file'                                  => "file",
    'report_template_removed'               => 'Removed {folder_or_file} <a class="table-color-orange deleted-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_before_event}</a>',
    'report_template_restored'              => 'Restored {folder_or_file} <a class="table-color-orange restored-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_after_event}</a>',
    'report_template_added'                 => 'Added new {folder_or_file} <a class="table-color-orange created-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_after_event}</a>',
    'report_template_modified'              => 'Modified {folder_or_file} <a class="table-color-orange updated-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_after_event}</a>',
    'report_template_moved'                 => 'Moved {folder_or_file} <a class="table-color-orange moved-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_after_event}</a> to <a class="table-color-orange moved-file" data-file-parent-id="{file_parent_id}" href="#">{parent_folder_name_after_event}</a>',
    'report_template_renamed'               => 'Renamed {folder_or_file} <a class="table-color-orange renamed-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_before_event}</a> to <a class="table-color-orange renamed-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_after_event}</a>',
    'report_template_moved_renamed'         => 'Moved and renamed {folder_or_file} <a class="table-color-orange moved-renamed-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_before_event}</a> to <a class="table-color-orange moved-renamed-file" data-file-parent-id="{file_parent_id}" href="#">{parent_folder_name_after_event}/{file_name_after_event}</a>',
    'report_template_restored_patch'        => 'Restored patch for {folder_or_file} <a class="table-color-orange updated-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_after_event}</a>',
    'report_template_collaboration_created' => 'Created collaboration for folder <a class="table-color-orange updated-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_after_event}</a>',
    'report_template_collaboration_deleted' => 'Canceled collaboration for folder <a class="table-color-orange updated-file" data-file-id="{file_id}" data-file-parent-id="{file_parent_id}" href="#">{file_name_after_event}</a>',
];