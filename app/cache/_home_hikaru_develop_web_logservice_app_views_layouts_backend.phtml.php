<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Logme - Admin</title>

        <!-- Bootstrap Core CSS -->
        <link href="/css/bootstrap.min.css" rel="stylesheet">
        <link href="/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
        <!-- Custom CSS -->
        <link href="/css/sb-admin.css" rel="stylesheet">
        
<!--         Morris Charts CSS 
        <link href="/css/plugins/morris.css" rel="stylesheet">-->

        <!-- Custom Fonts -->
        <link href="/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <!-- jQuery -->
        <script src="/js/jquery-1.11.2.min.js"></script>
    </head>
    <script type="text/javascript">
        $(function(){
            /**
             *  auto active menu
             */
            var uri = document.URL;
            var uri = uri.replace(/http:\/\/|https:\/\//i,'');
            var uri = uri.replace(/\?.*/,'');
            var args = uri.split('/');
            var active_menu =  (args[1]?'/'+args[1]:'') + (args[2]?'/'+args[2]:'');
            $('.side-nav li a[href="'+active_menu+'"]').parent().addClass('active');
        });
    </script>
    <body>
        <div id="wrapper">

            <!-- Navigation -->
            <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/admin">Logme Admin</a>
                </div>
                <!-- Top Menu Items -->
                <ul class="nav navbar-right top-nav">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $this->session->get('id');?> <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="/logout"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
                <div class="collapse navbar-collapse navbar-ex1-collapse">
                    <ul class="nav navbar-nav side-nav">
                        <li>
                            <a href="/admin"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
                        </li>
                        <li>
                            <a href="javascript:;" data-toggle="collapse" data-target="#dataChart"><i class="fa fa-fw fa-bar-chart-o"></i> Data Charts <i class="fa fa-fw fa-caret-down"></i></a>
                            <ul id="dataChart" class="collapse">
                                <li>
                                    <a href="/admin/chart/date">By Date</a>
                                </li>
                                <li>
                                    <a href="/admin/chart/month">By Month</a>
                                </li>
                                <li>
                                    <a href="/admin/chart/year">By Year</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript:;" data-toggle="collapse" data-target="#activeUserChart"><i class="fa fa-fw fa-bar-chart-o"></i> Active User Charts <i class="fa fa-fw fa-caret-down"></i></a>
                            <ul id="activeUserChart" class="collapse">
                                <li>
                                    <a href="/admin/active/date">By Date</a>
                                </li>
                                <li>
                                    <a href="/admin/active/month">By Month</a>
                                </li>
                                <li>
                                    <a href="/admin/active/year">By Year</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript:;" data-toggle="collapse" data-target="#demo"><i class="fa fa-fw fa-bar-chart-o"></i> Register Charts <i class="fa fa-fw fa-caret-down"></i></a>
                            <ul id="demo" class="collapse">
                                <li>
                                    <a href="/admin/register/date">By Date</a>
                                </li>
                                <li>
                                    <a href="/admin/register/month">By Month</a>
                                </li>
                                <li>
                                    <a href="/admin/register/year">By Year</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="/admin/logactionmap"><i class="fa fa-fw fa-wrench"></i> Log Action Map</a>
                        </li>
                        <li>
                            <a href="/admin/setting"><i class="fa fa-fw fa-wrench"></i> Setting For Report</a>
                        </li>
<!--                        <li>
                            <a href="tables.html"><i class="fa fa-fw fa-table"></i> Tables</a>
                        </li>
                        <li>
                            <a href="forms.html"><i class="fa fa-fw fa-edit"></i> Forms</a>
                        </li>
                        <li>
                            <a href="bootstrap-elements.html"><i class="fa fa-fw fa-desktop"></i> Bootstrap Elements</a>
                        </li>
                        <li>
                            <a href="bootstrap-grid.html"><i class="fa fa-fw fa-wrench"></i> Bootstrap Grid</a>
                        </li>-->
<!--                        <li>
                            <a href="javascript:;" data-toggle="collapse" data-target="#demo"><i class="fa fa-fw fa-arrows-v"></i> Dropdown <i class="fa fa-fw fa-caret-down"></i></a>
                            <ul id="demo" class="collapse">
                                <li>
                                    <a href="#">Dropdown Item</a>
                                </li>
                                <li>
                                    <a href="#">Dropdown Item</a>
                                </li>
                            </ul>
                        </li>-->
                        <!--<li>
                            <a href="blank-page.html"><i class="fa fa-fw fa-file"></i> Blank Page</a>
                        </li>
                        <li>
                            <a href="index-rtl.html"><i class="fa fa-fw fa-dashboard"></i> RTL Dashboard</a>
                        </li>-->
                    </ul>
                </div>
                <!-- /.navbar-collapse -->
            </nav>

            <div id="page-wrapper">
                <?php echo $this->getContent(); ?>
            </div>
            <!-- /#page-wrapper -->

        </div>
        <!-- /#wrapper -->
        <script type="text/javascript" src="/js/moment.js"></script>
        <script type="text/javascript" src="/js/transition.js"></script>
        <script type="text/javascript" src="/js/collapse.js"></script>
        <script type="text/javascript" src="/js/ajaxelement.js"></script>
        <script type="text/javascript" src="/js/jquery.form.js"></script>
        <!-- Bootstrap Core JavaScript -->
        <script type="text/javascript" src="/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/js/bootstrap-datetimepicker.min.js"></script>
    </body>

</html>