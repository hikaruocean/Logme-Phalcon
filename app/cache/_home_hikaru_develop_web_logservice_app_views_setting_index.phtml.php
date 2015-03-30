<script type="text/javascript">
    <?php echo $am_obj; ?>
    $(function() {
        $('#settingForm').ajaxForm({
            dataType:'json',
            success:function(json){
                if(json.result ==='200'){
                    alert('success');
                }
                else{
                    alert(json.msg);
                }
            }
        });
        $('#save').click(function(){
            $('#settingForm').submit();
        });
        $('.actionName').change(function() {
            var option_str = '<option value=""></option>';
            for (var i in am_obj[$(this).val()]) {
                option_str += '<option value="' + i + '">' + i + '</option>';
            }
            $(this).parents('.actionFrame').find('.actionColumn').html(option_str);
        });
        init();
    });
    function init() {
        $('.actionName').trigger('change');
        $('.registerFrame .actionColumn').val('<?php echo $registerJoinKey; ?>');
        $('.loginFrame .actionColumn').val('<?php echo $loginJoinKey; ?>');
    }
</script>
<div class="container-fluid">
    <form method="post" action="/admin/setting/save" name="settingForm" id="settingForm">
        <!-- Page Heading -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">
                    Setting For Report
                </h1>
                <ol class="breadcrumb">
                    <li>
                        <i class="fa fa-dashboard"></i>  <a href="/admin">Dashboard</a>
                    </li>
                    <li class="active">
                        <i class="fa fa-bar-chart-o"></i> Setting For Report
                    </li>
                </ol>
            </div>
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-6 actionFrame registerFrame">
                <div class="form-group">
                    <label>Select Register Log Action</label>
                    <select class="form-control actionName" name="reg_action">
                        <option value=""></option>
                        <?php foreach ($actionmap as $k => $v) { ?>
                        <option value="<?php echo $k; ?>" <?php echo ($k == $registerAction ? 'selected="selected"' : ''); ?>><?php echo $k; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Register JOIN KEY</label>
                    <select class="form-control actionColumn" name="reg_key">
                    </select>
                </div>
            </div>
            <div class="col-lg-6 actionFrame loginFrame">
                <div class="form-group">
                    <label>Select Login Log Action</label>
                    <select class="form-control actionName" name="log_action">
                        <option value=""></option>
                        <?php foreach ($actionmap as $k => $v) { ?>
                        <option value="<?php echo $k; ?>" <?php echo ($k == $loginAction ? 'selected="selected"' : ''); ?>><?php echo $k; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Login JOIN KEY</label>
                    <select class="form-control actionColumn" name="log_key">
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <button type="button" class="btn btn-lg btn-primary" id="save">Save</button>
            </div>
        </div>
    </form>
</div>