<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?=PAGE_TITLE_PREFIX?></title>

    <!-- Bootstrap Core CSS -->
    <link href="<?=SB_ADMIN_2?>vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=SB_ADMIN_2?>vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet">
    
    <!-- MetisMenu CSS -->
    <link href="<?=SB_ADMIN_2?>vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?=SB_ADMIN_2?>dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="<?=SB_ADMIN_2?>vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link href="<?=LIB_PATH?>kartik-v-bootstrap-fileinput/css/fileinput.min.css" rel="stylesheet" type="text/css">

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
                <a class="navbar-brand" href="<?=HTTP_ROOT?>">
                	<img src="https://raw.githubusercontent.com/liparig/D.I.DO/Logo/LOGO%20DIDO%20fede.png" style="height:100%;"/>
                </a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <li class="dropdown">
                    <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i><?=Utils::operatore(Session::getInstance()->get("AUTH_USER"));?>&nbsp;<i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="<?=HTTP_ROOT."?logout"?>"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                            <!-- /input-group -->
                        </li>
                        <?php TemplateHelper::LeftMenu()?>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>
        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="container-fluid">
            	<?php include( VIEWS_PATH. (isset($view) ? $view : basename($_SERVER['PHP_SELF'])));?>
            </div>
        </div>
    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="<?=SB_ADMIN_2?>vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="<?=SB_ADMIN_2?>vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?=SB_ADMIN_2?>vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="<?=SB_ADMIN_2?>vendor/bootstrap-datepicker/locales/bootstrap-datepicker.it.min.js" charset="UTF-8"></script>
    
    <!-- Metis Menu Plugin JavaScript -->
    <script src="<?=SB_ADMIN_2?>vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="<?=SB_ADMIN_2?>dist/js/sb-admin-2.js"></script>
	
	<!-- DataTables JavaScript -->
	<script src="<?=SB_ADMIN_2?>vendor/datatables/js/jquery.dataTables.min.js"></script>
	<script src="<?=SB_ADMIN_2?>vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
	<script src="<?=SB_ADMIN_2?>vendor/datatables-responsive/dataTables.responsive.js"></script>
	
	<?php if(defined("KARTIK_FILEINPUT")):?>
    <script src="<?=LIB_PATH?>kartik-v-bootstrap-fileinput/js/fileinput.min.js"></script>
    <script src="<?=LIB_PATH?>kartik-v-bootstrap-fileinput/js/locales/it.js"></script>
    <script>
	    $(".file").fileinput({
	        language: "it",
	    });
    </script>
    <?php endif; ?>
	
	<!-- Custom Scripts -->
	<?php 
		if(isset($pageScripts)){
			$pageScripts = implode(",", array_map('Utils::apici',$pageScripts));
			eval("Utils::includeScript(SCRIPTS_PATH, $pageScripts);");
		} 
	?>
		
</body>

</html>
