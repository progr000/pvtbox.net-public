<?php
use yii\helpers\Url;

return [
    'collaboration_include'       => '<span class="email">{user_email}</span> added you as a collaborator to <span class="folder">{folder_name}</span> folder with access to <span class="folder">{access_type}</span>. Click <a href="{include_link}">here</a> to view collaboration',
    'collaboration_invite'        => '<span class="email">{user_email}</span> invited you to be a collaborator of <span class="folder">{folder_name}</span> folder with access to <span class="folder">{access_type}</span>. Click <a href="{accept_link}">here</a> to accept the invitation and join collaboration',
    'collaboration_join'          => '<span class="email">You</span> successfully joined collaboration folder <span class="folder">{folder_name}</span> with access <span class="folder">{access_type}</span>. Click <a href="{joined_link}">here</a> to view collaboration',
    'collaboration_about_join_for_admin' => 'Your colleague <span class="email">{user_email}</span> successfully joined into collaboration folder <span class="folder">{folder_name}</span>',
    'collaboration_exclude'       => '<span class="email">{user_email}</span> removed your access to <span class="folder">{folder_name}</span> collaboration folder',
    'collaboration_self_exclude'  => '<span class="email">You</span> successfully left collaboration folder <span class="folder">{folder_name}</span>',
    'for_owner_colleague_self_exclude' => '<span class="email">{user_email}</span> left collaboration folder <span class="folder">{folder_name}</span>',
    'collaboration_change_access' => '<span class="email">{user_email}</span> set an access <span class="folder">{access_type}</span> to <span class="folder">{folder_name}</span> folder for you',
    'collaboration_added_files'   => 'Added new file <span class="folder">{file_name}</span> to <span class="folder">{folder_name}</span> folder',
    'collaboration_deleted_files' => 'Removed file <span class="folder">{file_name}</span> from <span class="folder">{folder_name}</span> folder',
    'collaboration_moved_files'   => 'Moved <span class="folder">{file_name}</span> file in <span class="folder">{folder_name}</span> folder',
    'license_expired'             => 'Your license ({license_type}) has expired on (till {license_expire}) and will be downgraded soon. Please follow the <a href="{pay_link}">link</a> to renew subscription.',
    'license_downgraded'          => 'Your license was downgraded from {OLD_LICENSE_TYPE} to {NEW_LICENSE_TYPE}. Collaborations with other users have been cancelled. <a href="' . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . '">Please upgrade</a> your license!',
    'license_upgraded'            => 'Your license was upgraded from {OLD_LICENSE_TYPE} to {NEW_LICENSE_TYPE}. Collaborations with other users have been cancelled.',
    'license_changed'             => 'Your license was changed from {OLD_LICENSE_TYPE} to {NEW_LICENSE_TYPE}. Collaborations with other users have been cancelled. '
];