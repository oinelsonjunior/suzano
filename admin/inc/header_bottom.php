<div class="m-header__bottom">
<div class="m-container m-container--responsive m-container--xxl m-container--full-height m-page__container">
    <div class="m-stack m-stack--ver m-stack--desktop">
        <div class="m-stack__item m-stack__item--middle m-stack__item--fluid">
            <button class="m-aside-header-menu-mobile-close m-aside-header-menu-mobile-close--skin-light"
                    id="m_aside_header_menu_mobile_close_btn">
                <i class="la la-close"></i>
            </button>
            <div id="m_header_menu"
                 class="m-header-menu m-aside-header-menu-mobile m-aside-header-menu-mobile--offcanvas
                        m-header-menu--skin-dark m-header-menu--submenu-skin-light
                        m-aside-header-menu-mobile--skin-light m-aside-header-menu-mobile--submenu-skin-light">
                <ul class="m-menu__nav m-menu__nav--submenu-arrow" id="mc-menu-nav">

                    <li class="m-menu__item" data-menu="dashboard" aria-haspopup="true">
                        <a href="index" class="m-menu__link">
                            <span class="m-menu__item-here"></span>
                            <span class="m-menu__link-text">Dashboard</span>
                        </a>
                    </li>

                    <li class="m-menu__item" data-menu="integrantes" aria-haspopup="true">
                        <a href="integrantes_faccao" class="m-menu__link">
                            <span class="m-menu__item-here"></span>
                            <span class="m-menu__link-text">Integrantes</span>
                        </a>
                    </li>

                    <li class="m-menu__item" data-menu="financeiro" aria-haspopup="true">
                        <a href="financeiro/dashboard" class="m-menu__link">
                            <span class="m-menu__item-here"></span>
                            <span class="m-menu__link-text">Financeiro</span>
                        </a>
                    </li>

                    <li class="m-menu__item" data-menu="frequencia" aria-haspopup="true">
                        <a href="frequencia/" class="m-menu__link">
                            <span class="m-menu__item-here"></span>
                            <span class="m-menu__link-text">Frequência</span>
                        </a>
                    </li>

                    <li class="m-menu__item" data-menu="eventos" aria-haspopup="true">
                        <a href="eventos/" class="m-menu__link">
                            <span class="m-menu__item-here"></span>
                            <span class="m-menu__link-text">Eventos</span>
                        </a>
                    </li>

                    <li class="m-menu__item" data-menu="usuarios" aria-haspopup="true">
                        <a href="usuarios/" class="m-menu__link">
                            <span class="m-menu__item-here"></span>
                            <span class="m-menu__link-text">Usuários</span>
                        </a>
                    </li>

                    <li class="m-menu__item" data-menu="disciplina" aria-haspopup="true">
                        <a href="disciplina/" class="m-menu__link">
                            <span class="m-menu__item-here"></span>
                            <span class="m-menu__link-text">Disciplina</span>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</div>
</div>

<style>
/* Item de menu ativo */
#mc-menu-nav > .m-menu__item.mc-ativo > .m-menu__link .m-menu__link-text {
    color: #ffffff !important;
    font-weight: 500;
}

/* Botão hamburger — só aparece no mobile */
.mc-hamburger {
    display: none;
    cursor: pointer;
    background: none;
    border: none;
    outline: none;
    padding: 8px;
    margin-left: 8px;
    vertical-align: middle;
}
.mc-hamburger .mc-bar,
.mc-hamburger .mc-bar::before,
.mc-hamburger .mc-bar::after {
    display: block;
    width: 22px;
    height: 2px;
    background: #ffffff;
    border-radius: 2px;
    position: relative;
    transition: all .25s ease;
}
.mc-hamburger .mc-bar::before,
.mc-hamburger .mc-bar::after {
    content: '';
    position: absolute;
    left: 0;
}
.mc-hamburger .mc-bar::before { top: -7px; }
.mc-hamburger .mc-bar::after  { top:  7px; }

@media (max-width: 992px) {
    .mc-hamburger { display: inline-block; }
}
</style>

<script>
(function() {
    // 1. Destacar item ativo no menu
    var path = window.location.pathname;
    var ativo = '';

    if (/\/index(\.php)?$/.test(path) || /\/admin\/?$/.test(path)) {
        ativo = 'dashboard';
    } else if (/\/integrante/.test(path)) {
        ativo = 'integrantes';
    } else if (/\/financeiro/.test(path)) {
        ativo = 'financeiro';
    } else if (/\/frequencia/.test(path)) {
        ativo = 'frequencia';
    } else if (/\/eventos/.test(path)) {
        ativo = 'eventos';
    } else if (/\/usuarios/.test(path)) {
        ativo = 'usuarios';
    } else if (/\/disciplina/.test(path)) {
        ativo = 'disciplina';
    }

    if (ativo) {
        var menuItem = document.querySelector('#mc-menu-nav [data-menu="' + ativo + '"]');
        if (menuItem) menuItem.classList.add('mc-ativo');
    }

    // 2. Injetar botão hamburger dentro do m-stack--inline do m-brand
    //    Estrutura: .m-brand > .m-stack--inline > [logo][AQUI]
    var brandStack = document.querySelector('.m-brand .m-stack--inline');
    if (brandStack) {
        var wrapper = document.createElement('div');
        wrapper.className = 'm-stack__item m-stack__item--middle';

        var btn = document.createElement('button');
        btn.id = 'm_aside_header_menu_mobile_toggle';
        btn.className = 'mc-hamburger m-brand__toggler';
        btn.setAttribute('aria-label', 'Abrir menu');
        btn.innerHTML = '<span class="mc-bar"></span>';

        wrapper.appendChild(btn);
        brandStack.appendChild(wrapper);
    }
})();
</script>