function checkIsFreeLicense(show_flash)
{
    if (UserLicense == 'FREE_DEFAULT') {
        if (show_flash) {
            flash_msg('license-restriction', 'error', 0, true, null, 'checkIsFreeLicense');
        }
        return true;
    }
    return false;
}
