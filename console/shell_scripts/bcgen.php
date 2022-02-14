<?php
/**
 * ВАЖНО!
 * Скрипт перезапишет оригиналы файлов на их компиленные версии.
 * Что бы случайно не закомитить это все в проект перед выполнением скрипта
 * удалить из проекта папку привязки к репозиторию (папка .git в корне проекта)
 * УДАЛИТСЯ АВТОМАТИЧЕСКИ (указана в переменной $DIRS_AND_FILES_FOR_DELETE_BEFORE)
 *
 * Параметр $PATH задает путь к корню проекта
 * Параметр $DIRS_AND_FILES_FOR_ENCODE_PHP позволяет перечислить те папки проекта которые нужно компилить
 *
 * Что бы случайно не слить кому-то важную информацию из конфигов
 * или не закомитить в проект скрипты которые уже скомпилены,
 * в скомпиленном проекте УДАЛЯЮТСЯ ПАПКИ И ФАЙЛЫ перечисленные в:
 * $DIRS_AND_FILES_FOR_DELETE_BEFORE
 * $DIRS_AND_FILES_FOR_DELETE_AFTER
 *
 * В рабочих конфигах системы удалить данные о наших подключениях к БД, редис и т.п.
 * Direct-link/console/config/ *
 * Direct-link/common/config/ *
 * Direct-link/backend/config/ *
 * Direct-link/frontend/config/ *
 *
 * Так же для распространяемого проекта нужно будет принудительно минифицировать CSS и JS скрипты
 * Минификация выполняется автоматически для всех файлов которые находятся
 * внутри перечисленных дирректорий в параметре $DIRS_MIN_JS_CSS
 */

namespace console\shell_scripts;

//$PATH = '/home/progr/tmp/eeeeeeeee/Direct-link/'; //set full path to project here
$PATH = realpath(__DIR__ . "/../../") . "/";

/* Подключаем нужные либы */
require_once $PATH . "common/helpers/JSMin.php";
require_once $PATH . "common/helpers/Functions.php";
require_once $PATH . "common/helpers/FileSys.php";
use common\helpers\Functions;
use common\helpers\FileSys;

/** список папок и файлов которые будут удалены еще до компиляции */
$DIRS_AND_FILES_FOR_DELETE_BEFORE = [
    '.git',
    '.gitignore',
    '.idea',
    'selfhosted',
    'tests',
    'codeception.yml.dist',
    'Razvertivanie.txt',
    'LICENSE.md',
    'README.md',

    'backend/config/main-local.php',
    'backend/config/main-local.php.dist',
    'backend/config/main-local.php.dlink.php',
    'backend/config/main-local.php.pvtbox.php',
    'backend/config/params-local.php',
    'backend/config/params-local.php.dist',
    'backend/config/params-local.php.dlink.php',
    'backend/config/params-local.php.pvtbox.php',
    'backend/runtime',
    'backend/tests',
    'backend/codeception.yml.dist',
    'backend/web/assets',

    'common/config/main-local.php',
    'common/config/main-local.php.dist',
    'common/config/main-local.php.dlink.php',
    'common/config/main-local.php.pvtbox.php',
    'common/config/main-local.php.boxtst.php',
    'common/config/params-local.php',
    'common/config/params-local.php.dist',
    'common/config/params-local.php.dlink.php',
    'common/config/params-local.php.pvtbox.php',
    'common/config/params-test.php',
    'common/config/tests-local.php.dist',
    'common/config/tests-local.php.pvtbox.php',
    'common/runtime',
    'common/tests',
    'common/codeception.yml.dist',

    'console/linux-configs',
    'console/config/main-local.php',
    'console/config/main-local.php.dist',
    'console/config/main-local.php.dlink.php',
    'console/config/main-local.php.pvtbox.php',
    'console/config/params-local.php',
    'console/config/params-local.php.dist',
    'console/config/params-local.php.dlink.php',
    'console/config/params-local.php.pvtbox.php',
    'console/runtime',

    'frontend/themes/null',
    'frontend/themes/orange',
    'frontend/themes/v20190812', // (ЭТО НАШ ТЕКУЩИЙ ДИЗАЙН, а распрастранять мы будем урезанный v-sh)
    'frontend/config/main-local.php',
    'frontend/config/main-local.php.dist',
    'frontend/config/main-local.php.dlink.php',
    'frontend/config/main-local.php.pvtbox.php',
    'frontend/config/params-local.php',
    'frontend/config/params-local.php.dist',
    'frontend/config/params-local.php.dlink.php',
    'frontend/config/params-local.php.pvtbox.php',
    'frontend/config/tests-local.php.dist',
    'frontend/config/tests-local.php.pvtbox.php',
    'frontend/runtime',
    'frontend/tests',
    'frontend/codeception.yml.dist',
    'frontend/web/blog', // нужно ли удалять блог???
    'frontend/web/testscripts',
    'frontend/web/themes/null',
    'frontend/web/themes/orange',
    'frontend/web/assets',
];

/** Файлы которые подлежат удалению в любом из каталогов проекта */
$FILES_FOR_DELETE_IN_ANY_DIRS = [
    '.git',
    '.gitignore',
    '.gitkeep',
    '.idea',
    'sitemap.xml',
    'robots.txt',
];

/** список папок и файлов которые будут удалены после компиляции */
$DIRS_AND_FILES_FOR_DELETE_AFTER = [
    'console/shell_scripts',
];

/** список папок в которых будут прокомпилированы все найденые файлы *.php */
$DIRS_AND_FILES_FOR_ENCODE_PHP = [
    'backend/assets',
    'backend/components',
    'backend/controllers',
    'backend/models',
    'backend/views',

    'common/helpers',
    'common/models',
    'common/widgets',

    'console/controllers',
    'console/migrations',

    'frontend/assets',
    'frontend/components',
    'frontend/controllers',
    'frontend/models',
    'frontend/modules',
    'frontend/themes', // ??? нужно ли компилить файлы дизайна пока не знаю
    'frontend/widgets',

    'common/config/params-local.php', //отдельный конфиг
    'common/config/params.php', //отдельный конфиг
    'common/config/main.php', //отдельный конфиг

    'console/config/params-local.php', //отдельный конфиг
    'console/config/params.php', //отдельный конфиг
    'console/config/main-local.php', //отдельный конфиг
    'console/config/main.php', //отдельный конфиг

    'backend/config/params-local.php', //отдельный конфиг
    'backend/config/params.php', //отдельный конфиг
    'backend/config/main-local.php', //отдельный конфиг
    'backend/config/main.php', //отдельный конфиг

    'frontend/config/params-local.php', //отдельный конфиг
    'frontend/config/params.php', //отдельный конфиг
    'frontend/config/main-local.php', //отдельный конфиг
    'frontend/config/main.php', //отдельный конфиг
];

/** список папок в которых будут минифицированы все найденые файлы *.css и *.js */
$DIRS_MIN_JS_CSS = [
    'backend/web/css',
    'backend/web/js',
    'frontend/web/themes',
];

/** конфиги которые необходимо переименовать в локальные версии для работы СХ */
$CONFIGS_FOR_RENAME = [
    'backend/config/main-local.php.v-sh.php' => 'backend/config/main-local.php',
    'backend/config/params-local.php.v-sh.php' => 'backend/config/params-local.php',
    'common/config/main-local.php.v-sh.php' => 'common/config/main-local.php',
    'common/config/params-local.php.v-sh.php' => 'common/config/params-local.php',
    'console/config/main-local.php.v-sh.php' => 'console/config/main-local.php',
    'console/config/params-local.php.v-sh.php' => 'console/config/params-local.php',
    'frontend/config/main-local.php.v-sh.php' => 'frontend/config/main-local.php',
    'frontend/config/params-local.php.v-sh.php' => 'frontend/config/params-local.php',
];

/** Папки которые нужно создать пустыми и дать на них права 0777 */
$DIRS_FOR_CREATE = [
    'backend/web/assets',
    'backend/runtime',
    'common/runtime',
    'console/runtime',
    'frontend/web/assets',
    'frontend/runtime',
];

/** функции */
/**
 * DELETE ITS FUNCTION FOR REAL ENCODING
 * @param string $src
 * @param string $dst
 */
function bcgen_compile_file($src, $dst)
{
    //file_put_contents($dst, 'ENCODED');
    file_put_contents($dst, file_get_contents($src));
    //echo "ENCODED\n";
}

/**
 * @param string $src
 * @param string $dst
 * @param bool $doBackup
 */
function bcEnc($src, $dst, $doBackup=false)
{
    if ($doBackup) {
        copy($src, $src . '.backup');
    }
    bcgen_compile_file($src, $dst);
}

/**
 * @param string $dir
 */
function executeEncoding($dir)
{
    if (file_exists($dir) && is_dir($dir)) {
        if ($h = @opendir($dir)) {
            while (false !== ($f = readdir($h))) {

                if (($f === '.') || ($f === '..'))
                    continue;

                $path = $dir.'/'.$f;
                if (!is_dir($path)) {
                    if (strrpos($path, '.php')) {
                        echo "Encode PHP '{$path}'\n";
                        bcEnc($path, $path);
                    }
                } else {
                    executeEncoding($path);
                }
            }
            closedir($h);
        }
    } elseif (file_exists($dir) && is_file($dir) && !is_link($dir) && strrpos($dir, '.php')) {
        bcEnc($dir, $dir);
    }
}

/**
 * @param string $dir
 */
function executeMinimisation($dir)
{
    if (file_exists($dir) && is_dir($dir)) {
        if ($h = @opendir($dir)) {
            while (false !== ($f = readdir($h))) {

                if (($f === '.') || ($f === '..'))
                    continue;

                $path = $dir.'/'.$f;
                if (!is_dir($path)) {
                    if (strrpos($path, '.min.') === false) {
                        if (strrpos($path, '.js')) {
                            echo "Minimize JS '{$path}'\n";
                            Functions::compressJs($path, $path);
                        }
                        if (strrpos($path, '.css')) {
                            echo "Minimize CSS '{$path}'\n";
                            Functions::compressCss($path, $path);
                        }
                    }
                } else {
                    executeMinimisation($path);
                }
            }
            closedir($h);
        }
    }
}

/**
 * @param $dir
 * @param $files_array
 */
function deleteDeniedFiles($dir, &$files_array)
{
    if (file_exists($dir) && is_dir($dir)) {
        if ($h = @opendir($dir)) {
            while (false !== ($f = readdir($h))) {

                if (($f === '.') || ($f === '..'))
                    continue;

                $path = $dir.'/'.$f;
                if (!is_dir($path)) {
                    if (in_array($f, $files_array)) {
                        echo "Delete '{$path}'\n";
                        unlink($path);
                    }
                } else {
                    deleteDeniedFiles($path, $files_array);
                }
            }
            closedir($h);
        }
    }
}

/**************************** BEGIN ***************************/
echo "\nSTARTED\n";

/**
 * START DELETE BEFORE ENCODING
 */
foreach ($DIRS_AND_FILES_FOR_DELETE_BEFORE as $v) {

    $v = $PATH . $v;
    if (file_exists($v)) {
        if (!is_link($v)) {
            echo "Delete '{$v}'\n";
            FileSys::remove($v, true);
        } else {
            echo "Unlink '{$v}'\n";
            unlink($v);
        }
    }
}

/**
 * START RENAME CONFIGS
 */
foreach ($CONFIGS_FOR_RENAME as $k => $v) {
    $source = $PATH . $k;
    $destination = $PATH . $v;
    if (file_exists($source)) {
        echo "Rename '{$k}' to '{$v}'\n";
        FileSys::move($source, $destination);
    }
}

/**
 * START CREATE SOME DIRS
 */
foreach ($DIRS_FOR_CREATE as $v) {
    $v = $PATH . $v;
    echo "Create dir '{$v}'\n";
    FileSys::mkdir($v, 0777);
    chmod($v, 0777);
}

/**
 * START ENCODING  all files *.php
 */
foreach ($DIRS_AND_FILES_FOR_ENCODE_PHP as $v) {

    $v = $PATH . $v;
    executeEncoding($v);

}

/**
 * START MINIMISATION  all files *.js, *.css
 */
foreach ($DIRS_MIN_JS_CSS as $v) {

    $v = $PATH . $v;
    executeMinimisation($v);

}

/**
 * START DELETE AFTER ENCODING
 */
foreach ($DIRS_AND_FILES_FOR_DELETE_AFTER as $v) {

    $v = $PATH . $v;
    if (file_exists($v)) {
        if (!is_link($v)) {
            echo "Delete '{$v}'\n";
            FileSys::remove($v, true);
        } else {
            echo "Unlink '{$v}'\n";
            unlink($v);
        }
    }
}

/**
 * START DELETE DENIED FILES
 */
deleteDeniedFiles($PATH, $FILES_FOR_DELETE_IN_ANY_DIRS);

echo "\nFINISHED\n";