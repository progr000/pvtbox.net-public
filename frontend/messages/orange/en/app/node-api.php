<?php
use yii\helpers\Url;

return [
    'No_actions_possible_at_Free_license'  => 'No actions possible at Free license. <a href="' . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . '">Please upgrade</a> your license!',
    'Login_limit_nodes' => "Your license limit nodes set to {license_limit_nodes}. Go to \"My Devices\" at " . Url::to(['/'], true) . " site, then remove unused devices.",
];