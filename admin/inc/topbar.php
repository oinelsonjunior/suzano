<div class="m-stack__item m-stack__item--fluid m-header-head" id="m_header_nav">

    <!-- Botão hamburger para menu mobile -->
    <button id="m_aside_header_menu_mobile_toggle"
            class="m-aside-header-menu-mobile-toggler m-aside-header-menu-mobile-toggler--active"
            style="display:none;">
        <span></span>
    </button>

    <div id="m_header_topbar" class="m-topbar m-stack m-stack--ver m-stack--general">
        <div class="m-stack__item m-topbar__nav-wrapper">
            <ul class="m-topbar__nav m-nav m-nav--inline">
                <li class="m-nav__item m-topbar__user-profile m-topbar__user-profile--img m-dropdown m-dropdown--medium m-dropdown--arrow m-dropdown--header-bg-fill m-dropdown--align-right m-dropdown--mobile-full-width m-dropdown--skin-light"
                    data-dropdown-toggle="click">
                    <a href="#" class="m-nav__link m-dropdown__toggle">
                        <span class="m-topbar__welcome">Olá,&nbsp;</span>
                        <span class="m-topbar__username"><?php echo $apelido; ?></span>
                    </a>
                    <div class="m-dropdown__wrapper">
                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                        <div class="m-dropdown__inner">
                            <div class="m-dropdown__header m--align-center" style="background-color:#000;">
                                <div class="m-card-user m-card-user--skin-dark">
                                    <div class="m-card-user__details">
                                        <span class="m-card-user__name m--font-weight-500">
                                            <?php echo $apelido; ?>
                                        </span>
                                        <a href="" class="m-card-user__email m--font-weight-300 m-link">
                                            <?php echo $email; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="m-dropdown__body">
                                <div class="m-dropdown__content">
                                    <ul class="m-nav m-nav--skin-light">
                                        <li class="m-nav__separator m-nav__separator--fit"></li>
                                        <li class="m-nav__item">
                                            <a href="../includes/logout"
                                               class="btn m-btn--pill btn-secondary m-btn m-btn--custom m-btn--label-brand m-btn--bolder">
                                                Sair
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>