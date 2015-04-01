<script src="/js/highcharts/highcharts.js"></script>
<script src="/js/highcharts/themes/dark-unica.js"></script>
<script src="/js/highcharts/modules/exporting.js"></script>
<script type="text/javascript">
    <?php echo $am_obj; ?>
    $(function() {
        init();
        $('#drawaction').click(function() {
            $('#drawForm').submit();
        });
        $(document).on('change', '.action', function() {
            $(this).parents('.condition_frame').find('.subcondition').remove();
            var option_str = '<option value=""></option>';
            if (!$(this).val()) {
                $(this).parents('.ActionLine').find('.sum').html(option_str);
            }
            else {
                for (var i in am_obj[$(this).val()]) {
                    if (am_obj[$(this).val()][i] === 'int' || am_obj[$(this).val()][i] === 'float') {
                        option_str += '<option value="' + i + '">' + i + '</option>';
                    }
                }
                $(this).parents('.ActionLine').find('.sum').html(option_str);
            }
        });
        $(document).on('click', '.addcondition', function() {
            var option_str = '<option value=""></option>';
            for (var i in am_obj[$('.action').val()]) {
                option_str += '<option value="' + i + '">' + i + '</option>';
            }
            var condition_html = '<p class="condition subcondition"><button type="button" class="btn btn-sm btn-danger delcondition">Del</button><label>&nbsp;Condition&nbsp;:&nbsp;</label><select class="field" name="field[]">' + option_str + '</select><label>&nbsp;Operator&nbsp;:&nbsp;</label><select class="operator" name="operator[]"><option value=""></option></select>&nbsp;<span class="valueFrame"><input type="text" name="value[]" class="value" placeholder="VALUE"/><span class="btSpan"><label>&nbsp;and&nbsp;</label><input type="text" name="valueBt[]" class="valueBt" placeholder="VALUE"/></span></span><span class="hright"><button type="button" class="btn btn-sm btn-primary addcondition">add</button></span></p>';
            $('.condition_frame').append(condition_html);
        });
        $(document).on('click', '.delcondition', function() {
            $(this).parents('.subcondition').remove();
        });
        $(document).on('change','.operator',function(){
            if($(this).val() === 'Ibtw' || $(this).val() === 'Dbtw'){
                $(this).parents('.subcondition').find('.btSpan').show();
            }
            else{
                $(this).parents('.subcondition').find('.btSpan').hide();
            }
        });
        $(document).on('change', '.field', function() {
            var dataType = {bool: 0, int: 1, float: 2, string: 3, isodate: 4};
            var option_str = '<option value=""></option>';
            $(this).parents('.subcondition').find('.value').val('');
            $(this).parents('.subcondition').find('.valueBt').val('');
            if (!$(this).val()) {

            }
            else {
                if ($(this).parents('.subcondition').find('.value').data('DateTimePicker')) {
                    $(this).parents('.subcondition').find('.value').data('DateTimePicker').destroy();
                }
                if ($(this).parents('.subcondition').find('.valueBt').data('DateTimePicker')) {
                    $(this).parents('.subcondition').find('.valueBt').data('DateTimePicker').destroy();
                }
                switch (dataType[am_obj[$('.action').val()][$(this).val()]]) {
                    case 0:
                        option_str += '<option value="Beq">equal</option>';
                        break;
                    case 1:
                    case 2:
                        option_str += '<option value="Ieq">equal</option>';
                        option_str += '<option value="Ine">notEqual</option>';
                        option_str += '<option value="Ilt">lessThan</option>';
                        option_str += '<option value="Ilte">lessThanEqual</option>';
                        option_str += '<option value="Igt">greaterThan</option>';
                        option_str += '<option value="Igte">greaterThanEqual</option>';
                        option_str += '<option value="Ibtw">between</option>';
                        break;
                    case 3:
                        option_str += '<option value="Seq">equal</option>';
                        option_str += '<option value="Sne">notEqual</option>';
                        option_str += '<option value="Ssubstr">substr</option>';
                        break;
                    case 4:
                        option_str += '<option value="Dlt">lessThan</option>';
                        option_str += '<option value="Dlte">lessThanEqual</option>';
                        option_str += '<option value="Dgt">greaterThan</option>';
                        option_str += '<option value="Dgte">greaterThanEqual</option>';
                        option_str += '<option value="Dbtw">between</option>';
                        $(this).parents('.subcondition').find('.value').datetimepicker({
                            format: 'YYYY-MM-DD HH:mm:00'
                        });
                        $(this).parents('.subcondition').find('.valueBt').datetimepicker({
                            format: 'YYYY-MM-DD HH:mm:59'
                        });
                        break;
                }
            }
            $(this).parents('.subcondition').find('.operator').html(option_str);
            $(this).parents('.subcondition').find('.operator').trigger('change');
        });
    });
    function init() {
        $('#drawForm').ajaxForm({
            dataType: 'json',
            success: function(json) {
                if (json.result === '200') {
                    $('#chartCanvas').highcharts(json.highchart);
                    totalChart(json.totalsum,json.totalcount);
                }
                else {
                    alert(json.msg);
                }
            }
        });
    }
    function totalChart(totalsum,totalcount) {
        $('#totalChartFrame').highcharts({
            chart: {
                type: 'bar'
            },
            title: {
                text: 'TOTAL RESULT'
            },
            subtitle: {
                text: 'Log Total Count: '+totalcount
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
                        ['Average', (totalsum/totalcount)]
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
                Data Charts
            </h1>
            <ol class="breadcrumb">
                <li>
                    <i class="fa fa-dashboard"></i>  <a href="/admin">Dashboard</a>
                </li>
                <li class="active">
                    <i class="fa fa-bar-chart-o"></i>Data Chart By <?php echo $range; ?>
                </li>
            </ol>
        </div>
    </div>
    <!-- /.row -->
    <div class="ActionLine form-group">
        <form name="drawForm" id="drawForm" method="post" action="/admin/chart/draw">
            <div class="condition_frame">
                <p class="condition">
                    <label>View Action : </label>
                    <select class="action" name="action">
                        <option value=""></option>
                        <?php foreach ($actionmap as $k => $v) { ?>
                        <option value="<?php echo $k; ?>"><?php echo $k; ?></option>
                        <?php } ?>
                    </select>
                    <label> Sum : </label>
                    <select class="sum" name="sum">
                        <option value=""></option>
                    </select>
                    <input type="hidden" name="range" value="<?php echo $range; ?>"/>
                    <span class="hright"><button type="button" class="btn btn-sm btn-primary addcondition">add</button></span>
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
                    <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Log Graph</h3>
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
                    <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Log Graph</h3>
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