<button class="btn up-btn fixed-btn js-fixed-btn js-scroll-top" type="button">
    <svg class="icon icon-arrow">
        <use xlink:href="#arrow"></use>
    </svg><span><?= Yii::t('app/common', "To_the_top") ?></span>
</button>
<script>
    window.onload = function() {
        hideSiteLoader();
    }
</script>
<script>
    //SVG Ajax Loading
    var ajax = new XMLHttpRequest();
    ajax.open("GET", "/assets/v20190812-min/images/sprite.svg", true);
    ajax.send();
    ajax.onload = function (e) {
        var block = document.createElement('div');
        block.innerHTML = ajax.responseText;
        block.classList.add('svg-sprite-wrap');
        document.body.insertBefore(block, document.body.childNodes[0]);
    };

    //SVG fix
    document.addEventListener("DOMContentLoaded", function () {
        var baseUrl = window.location.href
            .replace(window.location.hash, "");

        [].slice.call(document.querySelectorAll("use[*|href]"))

            .filter(function (element) {
                return (element.getAttribute("xlink:href").indexOf("#") === 0);
            })

            .forEach(function (element) {
                element.setAttribute("xlink:href", baseUrl + element.getAttribute("xlink:href"));
            });

    }, false);

    if (document.all) {
        var browsehappy = document.createElement('div');
        browsehappy.className = 'browsehappy';
        browsehappy.innerHTML = '<div class="container">You are using a <strong>too outdated version</strong> of the browser. Please <a href="http://browsehappy.com/?locale=en_ru">update your browser version</a> for more productive and comfortable work.</div>';
        document.body.appendChild(browsehappy);
    }
</script>
