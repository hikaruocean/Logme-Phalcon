<script src="/js/highcharts/highcharts.js"></script>
<script src="/js/highcharts/themes/dark-unica.js"></script>
<script src="/js/highcharts/modules/exporting.js"></script>
<script type="text/javascript">
    $(function() {
        $('#drawaction').click(function() {
            $('#drawForm').submit();
        });
        $(document).on('change', '.operator', function() {
            if ($(this).val() === 'Dbtw') {
                $(this).parents('.subcondition').find('.btSpan').show();
            }
            else {
                $(this).parents('.subcondition').find('.btSpan').hide();
            }
        });
        init();
    });
    function init() {
        $('.subcondition').find('.value').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:00'
        });
        $('.subcondition').find('.valueBt').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:59'
        });
        $('#drawForm').ajaxForm({
            dataType: 'json',
            success: function(json) {
                if (json.result === '200') {
                    $('#chartCanvas').highcharts(json.highchart);
                    totalChart(json.totalsum, json.totalcount);
                }
                else {
                    alert(json.msg);
                }
            }
        });
        <?php echo ($startDate && $endDate ? '$(".btSpan").show();' : ''); ?>
        $('#drawaction').trigger('click');
    }
    function totalChart(totalsum, totalcount) {
        $('#totalChartFrame').highcharts({
            chart: {
                type: 'bar'
            },
            title: {
                text: 'TOTAL RESULT'
            },
            subtitle: {
                text: 'Log Total Count: ' + totalcount
            },
            xAxis: {
                type: 'category',
                labels: {
                    rotation: -45,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Quantity'
                }
            },
            legend: {
                enabled: false
            },
            tooltip: {
                pointFormat: '<b>{point.y:.2f}</b>'
            },
            series: [{
                    name: 'Population',
                    data: [
                        ['Total Sum', totalsum],
                        ['Average', (totalsum / totalcount)]
                    ],
                    dataLabels: {
                        enabled: true,
                        rotation: 0,
                        color: '#FFFFFF',
                        align: 'right',
                        format: '{point.y:.2f}', // one decimal
                        y: 10, // 10 pixels down from the top
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Verdana, sans-serif'
                        }
                    }
                }]
        });
    }
</script>
<style>
    .ActionLine{
        background-color: #e6e6e6;
        padding: 12px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        -moz-box-shadow:4px 4px 3px rgba(20%,20%,40%,0.5);
        -webkit-box-shadow:4px 4px 3px rgba(20%,20%,40%,0.5);
        box-shadow:4px 4px 3px rgba(20%,20%,40%,0.5);
    }
    .condition{
        background-color: #ebcccc;
        padding: 12px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
    }
    .hright{
        float: right;
    }
    .valueFrame{
        position: relative;
    }
    .btSpan{
        display:none;
    }
</style>
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                 Active User Chart
            </h1>
            <ol class="breadcrumb">
                <li>
                    <i class="fa fa-dashboard"></i>  <a href="/admin">Dashboard</a>
                </li>
                <li class="active">
                    <i class="fa fa-bar-chart-o"></i> Active User Chart By <?php echo $range; ?>
                </li>
            </ol>
        </div>
    </div>
    <!-- /.row -->
    <div class="ActionLine form-group">
        <form name="drawForm" id="drawForm" method="post" action="/admin/active/draw">
            <div class="condition_frame">
                <p class="condition subcondition">
                    <label>&nbsp;Date Range&nbsp;:&nbsp;</label>
                    <select class="operator" name="operator">
                        <option value=""></option>
                        <option value="Dlt">lessThan</option>;
                        <option value="Dlte">lessThanEqual</option>;
                        <option value="Dgt">greaterThan</option>;
                        <option value="Dgte">greaterThanEqual</option>;
                        <option value="Dbtw" <?php echo ($startDate && $endDate ? 'selected="selected"' : ''); ?>>between</option>;
                    </select>
                    &nbsp;
                    <span class="valueFrame">
                        <input name="value" class="value" placeholder="VALUE" type="text" value="<?php echo $startDate; ?>"/>
                        <span class="btSpan">
                            <label>&nbsp;and&nbsp;</label>
                            <input name="valueBt" class="valueBt" placeholder="VALUE" type="text" value="<?php echo $endDate; ?>"/>
                        </span>
                    </span>
                    <input type="hidden" name="range" value="<?php echo $range; ?>"/>
                </p>
            </div>
            <button type="button" class="btn btn-sm btn-success" id="drawaction">Draw</button>
        </form>
    </div>
    <!-- /.row -->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-green">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Active User Graph</h3>
                </div>
                <div class="panel-body" style="background-color: #333333;">
                    <div id="chartCanvas"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Acitve User Graph</h3>
                </div>
                <div class="panel-body" style="background-color: #333333;">
                    <div id="totalChartFrame"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->

</div>
<!-- /.container-fluid -->