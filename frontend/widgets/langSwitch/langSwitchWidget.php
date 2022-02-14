<?php
namespace frontend\widgets\langSwitch;

use Yii;
use yii\base\Widget;
use common\models\Languages;

class langSwitchWidget extends Widget
{
    private $currentLanguage;
    private $listLanguages;
    public $currentUrl;
    private $tplUrl;

    public function init()
    {
        parent::init();

        $this->currentUrl = Yii::$app->request->getUrl();
        $this->currentLanguage = Yii::$app->language;
        $this->listLanguages = Languages::langLabels();

        //var_dump(Yii::$app->components['urlManager']['languages']); exit;

        /* подготовка списка языков*/
        if (isset($this->listLanguages[$this->currentLanguage])) {
            unset($this->listLanguages[$this->currentLanguage]);
        }
        foreach ($this->listLanguages as $k=>$v) {
            if (!in_array($k, Yii::$app->components['urlManager']['languages'])) {
                unset($this->listLanguages[$k]);
            }
        }

        /* подготовка шаблона юрла */
        $regexp = "/^\/" . $this->currentLanguage . "($|\/)/";
        if (preg_match($regexp, $this->currentUrl)) {
            $this->tplUrl = preg_replace($regexp, "/{LANG}/", $this->currentUrl);
        } else {
            $this->tplUrl = "/{LANG}" . $this->currentUrl;
        }
        //var_dump($this->currentUrl);
        //var_dump($this->tplUrl);exit;

    }

    public function run()
    {
        $vars = '';
        foreach ($this->listLanguages as $k=>$v) {
            $vars .= '<li><a href="' . str_replace('{LANG}', $k, $this->tplUrl) . '"><i class="' . $k . '"></i> ' . $v . '</a></li>';
        }
        if ($vars != '') {
            $vars = '<ul class="dropdown-menu">
                        ' . $vars . '
                     </ul>';
        }
        return '<div class="language dropup">
                    <span class="dropdown-toggle lng" data-toggle="dropdown">
                        <a class="lng__item void-0" href="#"><i class="' . $this->currentLanguage . '"></i> ' . Languages::langLabel($this->currentLanguage) . '</a>
                    </span>
                    '.$vars.'
                </div>'."\n";
    }
}
