<?php $site_name = $this->requestAction('settings/setting'); ?>
<?php $left_navigation = $this->requestAction('dashboards/dashboard_links'); ?>

<!DOCTYPE html>
<html>
<head>
    <?php echo $this->Html->charset(); ?>
    <title> <?php echo $site_name.': '. $this->fetch('title'); ?> </title>
    <?php
    echo $this->Html->meta('icon');

    $version = Configure::read('version');

    echo $this->Html->css(array(
        'admin_style.css?v='.$version,
        'bootstrap.min.3.0.3',
        'plugins/metisMenu/metisMenu.min',
        'plugins/timeline',
        'sb-admin-2',
        'jquery-ui',
        'admin_custom.css?v='.$version,
        'plugins/morris',
        'font-awesome.min'
    ));


    echo $this->fetch('meta');
    echo $this->fetch('css');
    echo "\n" . '<script type="text/javascript">var BASE_URL = \'' . $this->Html->url('/', true) . '\';</script>';
    echo "\n" . '<script type="text/javascript">var CONTROLLER = \'' . $this->name . '\';</script>';
    echo "\n" . '<script type="text/javascript">var ACTION = \'' . $this->action . '\';</script>';
    echo $this->fetch('script');
    echo $this->Html->script('jquery');
    echo $this->Html->script('jquery-ui');
    echo $this->Html->script('custom.js?v='.$version);
    echo $this->Html->script('jquery.validate');
    echo $this->Html->script('inflection');
    echo $this->Html->script('custom_admin.js?v='.$version);

    ?>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>

    <![endif]-->
</head>
<body>
<div id="wrapper">
    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <!--<a class="navbar-brand" href=""></a>-->
            <?php
            echo $this->Html->link($site_name,Router::url('/',true),array('class' => 'navbar-brand') );
            ?>
        </div>
        <ul class="nav navbar-top-links navbar-right">

            <?php
            if($this->Session->check('Auth.User')) {
                ?>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="<?php echo Router::url('/users/my_profile',true) ?>"><i class="fa fa-user fa-fw"></i>My Profile</a>
                        </li>
                        </li>
                        <li class="divider"></li>
                        <li><a href="<?php echo Router::url('/users/logout',true) ?>"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>

            <?php } ?>
            <!-- /.dropdown -->
        </ul>
        <!-- /.navbar-top-links -->

        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    <?php if($this->params['controller']=='EmailTemplates' and ($this->params['action']=='admin_create' or $this->params['action']=='admin_update') ){ ?>
                        <li>
                            <a href="#"><i class="fa fa-wrench fa-fw"></i> Buyer Info<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="panels-wells.html">Buyer Name</a>
                                </li>
                                <li>
                                    <a href="buttons.html">First Name</a>
                                </li>
                                <li>
                                    <a href="notifications.html">Buyer Email</a>
                                </li>

                            </ul>
                            <!-- /.nav-second-level -->
                        </li>

                        <li>
                            <a href="#"><i class="fa fa-wrench fa-fw"></i> Order Info<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="#">Product Name</a>
                                </li>
                                <li>
                                    <a href="#">Order Id</a>
                                </li>
                                <li>
                                    <a href="#">Msku</a>
                                </li>
                                <li>
                                    <a href="#">Asin</a>
                                </li>
                                <li>
                                    <a href="#">Quantity</a>
                                </li>
                                <li>
                                    <a href="#">Price Item</a>
                                </li>
                                <li>
                                    <a href="#">Price Shipping</a>
                                </li>
                                <li>
                                    <a href="#"> Condition Note</a>
                                </li>
                                <li>
                                    <a href="#">Order Id</a>
                                </li>
                                <li>
                                    <a href="#">Recipient</a>
                                </li>
                                <li>
                                    <a href="#"> Purchase Date</a>
                                </li>

                            </ul>
                            <!-- /.nav-second-level -->
                        </li>

                        <li>
                            <a href="#"><i class="fa fa-wrench fa-fw"></i>Shipping Info<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="#">Ship Address1</a>
                                </li>
                                <li>
                                    <a href="#">Ship Address2</a>
                                </li>
                                <li>
                                    <a href="#">Ship City</a>
                                </li>
                                <li>
                                    <a href="#">Ship State</a>
                                </li>
                                <li>
                                    <a href="#">Ship Zip</a>
                                </li>
                                <li>
                                    <a href="#">Ship Country</a>
                                </li>
                                <li>
                                    <a href="#">Carrier</a>
                                </li>
                                <li>
                                    <a href="#">Tracking Number</a>
                                </li>
                                <li>
                                    <a href="#">Estimated Arrival</a>
                                </li>

                            </ul>
                            <!-- /.nav-second-level -->
                        </li>

                    <?php } ?>

                    <li>
                        <a href="<?php echo $this->base;?>/admin/dashboards/display" class="<?php echo ($this->params['controller']=='dashboards' AND $this->params['action']=='admin_display')?'active':'' ?>"><i class="fa fa-wrench fa-fw"></i> Dashboard <span class="fa arrow"></span></a>
                    </li>
                    <?php

                    if(!empty($left_navigation)){

                       //pr($this->params['pass']); die;

                        if(isset($this->params['pass'][0])){
                            $pass = $this->params['pass'][0];
                        }else{
                            $pass = null;
                        }
                        foreach($left_navigation as $navigation){

                            if($navigation['Dashboard']['url'] == 'users'){
                                // Worker id 2
                                // Client id 3
                                ?>
                                <li><a href="<?php echo $this->base.'/admin/'.$navigation['Dashboard']['url'].'/index/2' ?>" class="<?php echo ($this->params['controller']==$navigation['Dashboard']['url'] and $pass == 2 )?'active':'' ?>"><i class="fa fa-wrench fa-fw"></i>Workers<span class="fa arrow"></span></a></li>
                                <li><a href="<?php echo $this->base.'/admin/'.$navigation['Dashboard']['url'].'/index/3' ?>" class="<?php echo ($this->params['controller']==$navigation['Dashboard']['url'] and $pass == 3 )?'active':'' ?>"><i class="fa fa-wrench fa-fw"></i>Clients<span class="fa arrow"></span></a></li>

                            <?php  continue; } ?>
                            <li><a href="<?php echo $this->base.'/admin/'.$navigation['Dashboard']['url'] ?>" class="<?php echo ($this->params['controller']==$navigation['Dashboard']['url'])?'active':'' ?>"><i class="fa fa-wrench fa-fw"></i><?php echo $navigation['Dashboard']['name'] ?><span class="fa arrow"></span></a></li>
                    <?php
                        }
                    }
                    ?>

                </ul>
            </div>
            <!-- /.sidebar-collapse -->
        </div>
        <!-- /.navbar-static-side -->
    </nav>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->Session->flash(); ?>
                <?php
                echo $this->fetch('content'); ?>
                <br/>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /#page-wrapper -->

</div>
<!-- /#wrapper -->


<?php echo $this->element('sql_dump'); ?>

<?php
echo $this->Html->script(
    array(
        'bootstrap.min.3.0.3',
        'plugins/metisMenu/metisMenu.min',
        'sb-admin-2'
    )
);

echo $this->Js->writeBuffer();
?>
</body>
</html>
