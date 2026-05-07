<?php
    session_start();
    require_once ('../inc/general.php');

    $mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');

$stmt = $conn->prepare("
SELECT 
    COUNT(*) as total_registros,
    SUM(pago = 1 AND isento = 0) as total_pagos,
    SUM(pago = 0 AND isento = 0) as total_pendentes,
    SUM(isento = 1) as total_isentos,

    SUM(CASE WHEN pago = 1 AND isento = 0 THEN valor_total ELSE 0 END) as total_arrecadado,
    SUM(CASE WHEN pago = 1 AND isento = 0 THEN valor_repasse ELSE 0 END) as total_repasse,
    SUM(CASE WHEN pago = 1 AND isento = 0 THEN valor_faccao ELSE 0 END) as total_faccao

FROM mensalidades
WHERE mes = ? AND ano = ?
");

$stmt->bind_param("ii", $mes, $ano);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();

// porcentagem
$percent_pago = $data['total_registros'] > 0 
    ? round(($data['total_pagos'] / $data['total_registros']) * 100)
    : 0;

$sql = "
SELECT SUM(valor_faccao) as caixa_total
FROM mensalidades
WHERE pago = 1
";

$res = $conn->query($sql);
$caixa = $res->fetch_assoc()['caixa_total'];

$sql = "SELECT SUM(valor) as total_saidas FROM caixa_saida";
$res = $conn->query($sql);
$total_saidas = $res->fetch_assoc()['total_saidas'] ?? 0;

$caixa_real = $caixa - $total_saidas;

?>
<!DOCTYPE html>
<html lang="pt-br" >
    <base href="../">
    <!-- begin::Head -->
    <head>
        <meta charset="utf-8" />
        <title>
            Abutre's MC | Sistema de Gestão
        </title>
        <meta name="description" content="Latest updates and statistic charts">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <!--begin::Web font -->
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>
          WebFont.load({
            google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
            active: function() {
                sessionStorage.fonts = true;
            }
          });
        </script>
        <!--end::Web font -->
        <!--begin::Base Styles -->  
        <!--begin::Page Vendors -->
        <!--end::Page Vendors -->
        <link href="css/style.bundle.css" rel="stylesheet" type="text/css" />
        <!--end::Base Styles -->
        <link rel="shortcut icon" href="assets/demo/demo2/media/img/logo/favicon.ico" />
    </head>
    <!-- end::Head -->
    <!-- end::Body -->
    <body class="m-page--wide m-header--fixed m-header--fixed-mobile m-footer--push m-aside--offcanvas-default"  >
        <!-- begin:: Page -->
        <div class="m-grid m-grid--hor m-grid--root m-page">
            <!-- begin::Header -->
            <header class="m-grid__item     m-header "  data-minimize="minimize" data-minimize-offset="200" data-minimize-mobile-offset="200" >
                <div class="m-header__top">
                    <div class="m-container m-container--responsive m-container--xxl m-container--full-height m-page__container">
                        <div class="m-stack m-stack--ver m-stack--desktop">
                            <!-- begin::Brand -->
                            <div class="m-stack__item m-brand">
                                <div class="m-stack m-stack--ver m-stack--general m-stack--inline">
                                    <div class="m-stack__item m-stack__item--middle m-brand__logo">
                                        <a href="index" class="m-brand__logo-wrapper">
                                            <img alt="" src="images/logo.png"/>
                                        </a>
                                    </div>
                                    
                                </div>
                            </div>
                            <!-- end::Brand -->     
                            <!-- begin::Topbar -->
                            <?php include_once('../inc/topbar.php'); ?>
                            <!-- end::Topbar -->
                        </div>
                    </div>
                </div>
                <!-- HEADER BOTTOM () -->
                <?php include_once('../inc/header_bottom.php'); ?>
                
            </header>
            <!-- end::Header -->        
        <!-- begin::Body -->
            <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor-desktop m-grid--desktop m-body">
                <div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver    m-container m-container--responsive m-container--xxl m-page__container">
                    <div class="m-grid__item m-grid__item--fluid m-wrapper">
                        <!-- BEGIN: Subheader -->
                        <div class="m-subheader ">
                            <div class="d-flex align-items-center">
                                <div class="mr-auto">
                                    <h3 class="m-subheader__title " style="text-transform: uppercase;">
                                        Financeiro - <?= $mes ?>/<?= $ano ?>
                                    </h3>
                                </div>
                                
                            </div>
                        </div>
                        <!-- END: Subheader -->
                        <div class="m-content">
                            <div class="m-portlet ">
                                <div class="m-portlet__body  m-portlet__body--no-padding">
                                    <div class="row m-row--no-padding m-row--col-separator-xl">
                                        <div class="col-md-12 col-lg-12 col-xl-12">
                                            <!-- FILTRO -->
                                            <form class="aponta mb-3">
                                                <input type="number" name="mes" class="form-control mr-2" value="<?= $mes ?>" min="1" max="12" style="max-width: 30%;float: left;">
                                                <input type="number" name="ano" class="form-control mr-2" value="<?= $ano ?>" style="max-width: 30%;float: left;">
                                                <button class="btn btn-dark" style="max-width: 30%;float: left;">Filtrar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="m-portlet">
                                <div class="m-portlet__body  m-portlet__body--no-padding">
                                    <div class="row m-row--no-padding m-row--col-separator-xl">
                                        <div class="col-xl-4">
                                            <!--begin:: Widgets/Stats2-1 -->
                                            <div class="m-widget14">
                                                <div class="m-widget14__header m--margin-bottom-30">
                                                    <h3 class="m-widget14__title">
                                                        Resumo financeiro
                                                    </h3>
                                                    
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="card bg-primary text-white p-3" style="width: 100%;">
                                                        Arrecadado<br>
                                                        <strong>R$ <?= number_format($data['total_arrecadado'],2,',','.') ?></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="card bg-danger text-white p-3" style="width: 100%;">
                                                        Repasse<br>
                                                        <strong>R$ <?= number_format($data['total_repasse'],2,',','.') ?></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="card bg-info text-white p-3" style="width: 100%;">
                                                        Facção<br>
                                                        <strong>R$ <?= number_format($data['total_faccao'],2,',','.') ?></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end:: Widgets/Stats2-1 -->
                                        </div>
                                        <div class="col-xl-4">
                                            <!--begin:: Widgets/Daily Sales-->
                                            <div class="m-widget14">
                                                <div class="m-widget14__header m--margin-bottom-30">
                                                    <h3 class="m-widget14__title">
                                                        Daily Sales
                                                    </h3>
                                                    
                                                </div>
                                                <div class="m-widget14__chart" style="height:120px;"><div class="chartjs-size-monitor" style="position: absolute; inset: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;"><div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div></div><div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:200%;height:200%;left:0; top:0"></div></div></div>
                                                    <canvas id="m_chart_daily_sales" width="467" height="120" class="chartjs-render-monitor" style="display: block; width: 467px; height: 120px;"></canvas>
                                                </div>
                                            </div>
                                            <!--end:: Widgets/Daily Sales-->
                                        </div>
                                        <div class="col-xl-4">
                                            <!--begin:: Widgets/Profit Share-->
                                            <div class="m-widget14">
                                                <div class="m-widget14__header">
                                                    <h3 class="m-widget14__title">
                                                        Visão Geral
                                                    </h3>
                                                    
                                                </div>
                                                <div class="row  align-items-center">
                                                    
                                                        <canvas id="graficoFinanceiro"></canvas>
                                                    
                                                </div>
                                            </div>
                                            <!--end:: Widgets/Profit Share-->
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-xl-4">
                                <!--begin:: Widgets/Top Products-->
                                <div class="m-portlet m-portlet--bordered-semi m-portlet--full-height ">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text">
                                                    Trends
                                                </h3>
                                            </div>
                                        </div>
                                        <div class="m-portlet__head-tools">
                                            <ul class="m-portlet__nav">
                                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
                                                    <a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand">
                                                        All
                                                    </a>
                                                    <div class="m-dropdown__wrapper">
                                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: auto; right: 36.5px;"></span>
                                                        <div class="m-dropdown__inner">
                                                            <div class="m-dropdown__body">
                                                                <div class="m-dropdown__content">
                                                                    <ul class="m-nav">
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Activity
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Messages
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    FAQ
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Support
                                                                                </span>
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
                                    <div class="m-portlet__body">
                                        <!--begin::Widget5-->
                                        <div class="m-widget4">
                                            <div class="m-widget4__chart m-portlet-fit--sides m--margin-top-10 m--margin-top-20" style="height:260px;"><div class="chartjs-size-monitor" style="position: absolute; inset: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;"><div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div></div><div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:200%;height:200%;left:0; top:0"></div></div></div>
                                                <canvas id="m_chart_trends_stats" width="510" height="260" class="chartjs-render-monitor" style="display: block; width: 510px; height: 260px;"></canvas>
                                            </div>
                                            <div class="m-widget4__item">
                                                <div class="m-widget4__img m-widget4__img--logo">
                                                    <img src="assets/app/media/img/client-logos/logo3.png" alt="">
                                                </div>
                                                <div class="m-widget4__info">
                                                    <span class="m-widget4__title">
                                                        Phyton
                                                    </span>
                                                    <br>
                                                    <span class="m-widget4__sub">
                                                        A Programming Language
                                                    </span>
                                                </div>
                                                <span class="m-widget4__ext">
                                                    <span class="m-widget4__number m--font-danger">
                                                        +$17
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="m-widget4__item">
                                                <div class="m-widget4__img m-widget4__img--logo">
                                                    <img src="assets/app/media/img/client-logos/logo1.png" alt="">
                                                </div>
                                                <div class="m-widget4__info">
                                                    <span class="m-widget4__title">
                                                        FlyThemes
                                                    </span>
                                                    <br>
                                                    <span class="m-widget4__sub">
                                                        A Let's Fly Fast Again Language
                                                    </span>
                                                </div>
                                                <span class="m-widget4__ext">
                                                    <span class="m-widget4__number m--font-danger">
                                                        +$300
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="m-widget4__item">
                                                <div class="m-widget4__img m-widget4__img--logo">
                                                    <img src="assets/app/media/img/client-logos/logo2.png" alt="">
                                                </div>
                                                <div class="m-widget4__info">
                                                    <span class="m-widget4__title">
                                                        AirApp
                                                    </span>
                                                    <br>
                                                    <span class="m-widget4__sub">
                                                        Awesome App For Project Management
                                                    </span>
                                                </div>
                                                <span class="m-widget4__ext">
                                                    <span class="m-widget4__number m--font-danger">
                                                        +$6700
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                        <!--end::Widget 5-->
                                    </div>
                                </div>
                                <!--end:: Widgets/Top Products-->
                            </div>
                            <div class="col-xl-4">
                                <!--begin:: Widgets/Activity-->
                                <div class="m-portlet m-portlet--bordered-semi m-portlet--widget-fit m-portlet--full-height m-portlet--skin-light ">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text m--font-light">
                                                    Activity
                                                </h3>
                                            </div>
                                        </div>
                                        <div class="m-portlet__head-tools">
                                            <ul class="m-portlet__nav">
                                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover">
                                                    <a href="#" class="m-portlet__nav-link m-portlet__nav-link--icon m-portlet__nav-link--icon-xl">
                                                        <i class="fa fa-genderless m--font-light"></i>
                                                    </a>
                                                    <div class="m-dropdown__wrapper">
                                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                                        <div class="m-dropdown__inner">
                                                            <div class="m-dropdown__body">
                                                                <div class="m-dropdown__content">
                                                                    <ul class="m-nav">
                                                                        <li class="m-nav__section m-nav__section--first">
                                                                            <span class="m-nav__section-text">
                                                                                Quick Actions
                                                                            </span>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Activity
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Messages
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    FAQ
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__item">
                                                                            <a href="" class="m-nav__link">
                                                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                                                <span class="m-nav__link-text">
                                                                                    Support
                                                                                </span>
                                                                            </a>
                                                                        </li>
                                                                        <li class="m-nav__separator m-nav__separator--fit"></li>
                                                                        <li class="m-nav__item">
                                                                            <a href="#" class="btn btn-outline-danger m-btn m-btn--pill m-btn--wide btn-sm">
                                                                                Cancel
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
                                    <div class="m-portlet__body">
                                        <div class="m-widget17">
                                            <div class="m-widget17__visual m-widget17__visual--chart m-portlet-fit--top m-portlet-fit--sides m--bg-danger">
                                                <div class="m-widget17__chart" style="height:320px;"><div class="chartjs-size-monitor" style="position: absolute; inset: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;"><div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div></div><div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:200%;height:200%;left:0; top:0"></div></div></div>
                                                    <canvas id="m_chart_activities" width="510" height="208" class="chartjs-render-monitor" style="display: block; width: 510px; height: 208px;"></canvas>
                                                </div>
                                            </div>
                                            <div class="m-widget17__stats">
                                                <div class="m-widget17__items m-widget17__items-col1">
                                                    <div class="m-widget17__item">
                                                        <span class="m-widget17__icon">
                                                            <i class="flaticon-truck m--font-brand"></i>
                                                        </span>
                                                        <span class="m-widget17__subtitle">
                                                            Delivered
                                                        </span>
                                                        <span class="m-widget17__desc">
                                                            15 New Paskages
                                                        </span>
                                                    </div>
                                                    <div class="m-widget17__item">
                                                        <span class="m-widget17__icon">
                                                            <i class="flaticon-paper-plane m--font-info"></i>
                                                        </span>
                                                        <span class="m-widget17__subtitle">
                                                            Reporeted
                                                        </span>
                                                        <span class="m-widget17__desc">
                                                            72 Support Cases
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="m-widget17__items m-widget17__items-col2">
                                                    <div class="m-widget17__item">
                                                        <span class="m-widget17__icon">
                                                            <i class="flaticon-pie-chart m--font-success"></i>
                                                        </span>
                                                        <span class="m-widget17__subtitle">
                                                            Ordered
                                                        </span>
                                                        <span class="m-widget17__desc">
                                                            72 New Items
                                                        </span>
                                                    </div>
                                                    <div class="m-widget17__item">
                                                        <span class="m-widget17__icon">
                                                            <i class="flaticon-time m--font-danger"></i>
                                                        </span>
                                                        <span class="m-widget17__subtitle">
                                                            Arrived
                                                        </span>
                                                        <span class="m-widget17__desc">
                                                            34 Upgraded Boxes
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end:: Widgets/Activity-->
                            </div>
                            <div class="col-xl-4">
                                <!--begin:: Widgets/Blog-->
                                <div class="m-portlet m-portlet--bordered-semi m-portlet--full-height ">
                                    <div class="m-portlet__head m-portlet__head--fit">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-action">
                                                <button type="button" class="btn btn-sm m-btn--pill  btn-brand">
                                                    Blog
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <div class="m-widget19">
                                            <div class="m-widget19__pic m-portlet-fit--top m-portlet-fit--sides" style="min-height-: 286px">
                                                <img src="assets/app/media/img//blog/blog1.jpg" alt="">
                                                <h3 class="m-widget19__title m--font-light">
                                                    Introducing New Feature
                                                </h3>
                                                <div class="m-widget19__shadow"></div>
                                            </div>
                                            <div class="m-widget19__content">
                                                <div class="m-widget19__header">
                                                    <div class="m-widget19__user-img">
                                                        <img class="m-widget19__img" src="assets/app/media/img//users/user1.jpg" alt="">
                                                    </div>
                                                    <div class="m-widget19__info">
                                                        <span class="m-widget19__username">
                                                            Anna Krox
                                                        </span>
                                                        <br>
                                                        <span class="m-widget19__time">
                                                            UX/UI Designer, Google
                                                        </span>
                                                    </div>
                                                    <div class="m-widget19__stats">
                                                        <span class="m-widget19__number m--font-brand">
                                                            18
                                                        </span>
                                                        <span class="m-widget19__comment">
                                                            Comments
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="m-widget19__body">
                                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry scrambled it to make text of the printing and typesetting industry scrambled a type specimen book text of the dummy text of the printing printing and typesetting industry scrambled dummy text of the printing.
                                                </div>
                                            </div>
                                            <div class="m-widget19__action">
                                                <button type="button" class="btn m-btn--pill btn-secondary m-btn m-btn--hover-brand m-btn--custom">
                                                    Read More
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end:: Widgets/Blog-->
                            </div>
                        </div>

                            
                            



<a href="financeiro/pendentes.php?mes=<?= $mes ?>&ano=<?= $ano ?>" 
class="btn btn-warning mb-3">
Ver Pendentes
</a>

<div class="row">

    <div class="card bg-danger p-3">
Saídas<br>
<strong>R$ <?= number_format($total_saidas,2,',','.') ?></strong>
</div>

<div class="card bg-success p-3">
Caixa Real<br>
<strong>R$ <?= number_format($caixa_real,2,',','.') ?></strong>
</div>

    <div class="card bg-info p-3">
Caixa acumulado<br>
<strong>R$ <?= number_format($caixa,2,',','.') ?></strong>
</div>

<div class="col-md-3">
<div class="card bg-dark text-white p-3">
Total<br>
<strong><?= $data['total_registros'] ?></strong>
</div>
</div>

<div class="col-md-3">
<div class="card bg-success text-white p-3">
Pagos<br>
<strong><?= $data['total_pagos'] ?> (<?= $percent_pago ?>%)</strong>
</div>
</div>

<div class="col-md-3">
<div class="card bg-warning text-white p-3">
Pendentes<br>
<strong><?= $data['total_pendentes'] ?></strong>
</div>
</div>

<div class="col-md-3">
<div class="card bg-secondary text-white p-3">
Isentos<br>
<strong><?= $data['total_isentos'] ?></strong>
</div>
</div>

</div>

<hr>

<div class="row">

<div class="col-md-4">
<div class="card bg-primary text-white p-3">
Arrecadado<br>
<strong>R$ <?= number_format($data['total_arrecadado'],2,',','.') ?></strong>
</div>
</div>

<div class="col-md-4">
<div class="card bg-danger text-white p-3">
Repasse<br>
<strong>R$ <?= number_format($data['total_repasse'],2,',','.') ?></strong>
</div>
</div>

<div class="col-md-4">
<div class="card bg-info text-white p-3">
Facção<br>
<strong>R$ <?= number_format($data['total_faccao'],2,',','.') ?></strong>
</div>
</div>

</div>

<hr>



</div>
                            <!--begin:: Widgets/Stats-->
                            <div class="m-portlet ">
                                <div class="m-portlet__body  m-portlet__body--no-padding">
                                    <div class="row m-row--no-padding m-row--col-separator-xl">
                                        <div class="col-md-12 col-lg-6 col-xl-3">
                                            <!--begin::Total Profit-->
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">
                                                        Caixa
                                                    </h4>
                                                    <br>
                                                    <span class="m-widget24__desc">
                                                        Valor bruto
                                                    </span>
                                                    <span class="m-widget24__stats m--font-brand">
                                                        R$ 317,00
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-brand" role="progressbar" style="width: 78%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="m-widget24__change">
                                                        Comparação mês passado
                                                    </span>
                                                    <span class="m-widget24__number">
                                                        78%
                                                    </span>
                                                </div>
                                            </div>
                                            <!--end::Total Profit-->
                                        </div>
                                        <div class="col-md-12 col-lg-6 col-xl-3">
                                            <!--begin::New Feedbacks-->
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">
                                                        Total de Integrantes
                                                    </h4>
                                                    <br>
                                                    <span class="m-widget24__desc">
                                                        Todos 
                                                    </span>
                                                    <span class="m-widget24__stats m--font-info">
                                                        <?php echo $total_integrantes; ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-info" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="m-widget24__change">
                                                        
                                                    </span>
                                                    <span class="m-widget24__number">
                                                        100%
                                                    </span>
                                                </div>
                                            </div>
                                            <!--end::New Feedbacks-->
                                        </div>
                                        <div class="col-md-12 col-lg-6 col-xl-3">
                                            <!--begin::New Orders-->
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">
                                                        Integrantes Ativos
                                                    </h4>
                                                    <br>
                                                    <span class="m-widget24__desc">
                                                        Em situação padrão
                                                    </span>
                                                    <span class="m-widget24__stats m--font-danger">
                                                        <?php echo $total_integrantes_ativos; ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-danger" role="progressbar" style="width: <?php echo $pc_total_integrantes_ativos; ?>%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="m-widget24__change">
                                                        
                                                    </span>
                                                    <span class="m-widget24__number">
                                                        <?php echo $pc_total_integrantes_ativos; ?> %
                                                    </span>
                                                </div>
                                            </div>
                                            <!--end::New Orders-->
                                        </div>
                                        <div class="col-md-12 col-lg-6 col-xl-3">
                                            <!--begin::New Users-->
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">
                                                        Afastadados / Desligados
                                                    </h4>
                                                    <br>
                                                    <span class="m-widget24__desc">
                                                        Afastamento ou Desligamento
                                                    </span>
                                                    <span class="m-widget24__stats m--font-success">
                                                        <?php echo $total_integrantes_afastados_desligados; ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-success" role="progressbar" style="width: <?php echo $pc_total_integrantes_afastados_desligados; ?>%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="m-widget24__change">
                                                        
                                                    </span>
                                                    <span class="m-widget24__number">
                                                        <?php echo $pc_total_integrantes_afastados_desligados; ?> %
                                                    </span>
                                                </div>
                                            </div>
                                            <!--end::New Users-->
                                        </div>

                                        
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="m-portlet ">
                                    <div class="col-md-12 col-lg-12 col-xl-12">
                                            <h4 class="m-widget24__title">
                                                Caixa
                                            </h4>
                                            <canvas id="grafico2"></canvas>
                                    </div>
                                </div>  
                            </div>
                            
                            <div class="col-xl-4">
                                <div class="m-portlet ">
                                    <div class="col-md-12 col-lg-12 col-xl-12">
                                            <h4 class="m-widget24__title">
                                                Integrantes
                                            </h4>
                                            <canvas id="grafico"></canvas>
                                    </div>
                                </div>  
                            </div>

                            <div class="col-xl-4">
                                <div class="m-portlet ">
                                    <div class="col-md-12 col-lg-12 col-xl-12">
                                            <h4 class="m-widget24__title">
                                                Frequência
                                            </h4>
                                            <canvas id="grafico3"></canvas>
                                    </div>
                                </div>  
                            </div>

                        </div>


                    </div>
                </div>
            </div>
            <!-- end::Body -->
            <!-- begin::Footer -->
            <?php require_once ('../inc/footer.php'); ?>
            <!-- end::Footer -->
        </div>
        <!-- end:: Page -->
                    <!-- begin::Quick Sidebar -->
        
        <!-- end::Quick Sidebar -->         
        <!-- begin::Scroll Top -->
        <div class="m-scroll-top m-scroll-top--skin-top" data-toggle="m-scroll-top" data-scroll-offset="500" data-scroll-speed="300">
            <i class="la la-arrow-up"></i>
        </div>
        <!-- end::Scroll Top -->            <!-- begin::Quick Nav -->
        
        <!-- begin::Quick Nav -->   
        <!--begin::Base Scripts -->
        <script src="js/vendors.bundle.js" type="text/javascript"></script>
        <script src="js/scripts.bundle.js" type="text/javascript"></script>
        <!--end::Base Scripts -->   
        <!--begin::Page Vendors -->
        
        <!--end::Page Vendors -->  
        <!--begin::Page Snippets -->
        <script src="js/dashboard.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!--end::Page Snippets -->
    </body>
    <!-- end::Body -->
</html>


<script>
new Chart(document.getElementById('graficoFinanceiro'), {
    type: 'doughnut',
    data: {
        labels: ['Pagos','Pendentes','Isentos'],
        datasets: [{
            data: [
                <?= $data['total_pagos'] ?>,
                <?= $data['total_pendentes'] ?>,
                <?= $data['total_isentos'] ?>
            ]
        }]
    }
});
</script>