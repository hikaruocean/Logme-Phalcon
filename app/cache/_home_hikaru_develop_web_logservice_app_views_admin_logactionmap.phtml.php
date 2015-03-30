<?php
$dataType_ary = [
'bool',
 'int',
 'float',
 'string',
 'isodate'
];
$dataType_option = '';
foreach ($dataType_ary as $dataType) {
$dataType_option.='<option value="' . $dataType . '">' . $dataType . '</option>';
}
$addKeyHtml = <<<HTML
<tr>
    <td class="amkey">
        <span style="display: none;" class="amview"></span>
        <span style="display: inline;" class="amedit">
            <button type="button" class="btn btn-xs btn-danger delkey">Del</button>
            <input name="key[]" value="" type="text">
        </span>
    </td>
    <td class="amtype">
        <span style="display: none;" class="amview"></span>
        <span style="display: inline;" class="amedit">
            <select name="value[]">
                <{dataTypeOption}>
            </select>
        </span>
    </td>
</tr>
HTML;
$addKeyHtml = str_replace(PHP_EOL, '', str_replace('<{dataTypeOption}>', $dataType_option, $addKeyHtml));
?>
<script type="text/javascript">
    $(function() {
        init();
        $('.goamedit').click(function() {
            $(this).parents('.actionMapFrame').find('.amedit').show();
            $(this).parents('.actionMapFrame').find('.amview').hide();
        });
        $('.canceledit').click(function() {
            var obj = $(this).parents('.actionMapFrame');
            obj.find('.amedit').hide();
            obj.find('.amview').show();
        });
        $('.goamsave').click(function() {
            var obj = $(this).parents('.actionMapFrame');
            json = ajaxElement(obj, '/admin/logactionmap/params/save', 'json');
            if (json.result === '200') {
                alert('success');
                obj.find('tbody tr').each(function() {
                    $(this).find('.amkey .amview').html($(this).find('.amkey .amedit input').val());
                    $(this).find('.amtype .amview').html($(this).find('.amtype .amedit select').val());
                });
                obj.find('.amedit').hide();
                obj.find('.amview').show();
            }
            else {
                alert(json.msg);
            }
        });
        $('.addkey').click(function() {
            $(this).parents('.actionMapFrame').find('table').append('<?php echo $addKeyHtml; ?>');
        });
        $(document).on('click', '.delkey', function() {
            $(this).parents('tr').remove();
        });
        $('#addaction').click(function() {
            var json = ajaxElement($('.addActionFrame'), '/admin/logactionmap/action/save', 'json');
            if (json.result === '200') {
                alert('success');
                location.href = '/admin/logactionmap?search=' + $('#action').val();
            }
            else {
                alert(json.msg);
            }
        });
        $('.gosearch').click(function() {
            var key = $('.searchvalue').val();
            if (key) {
                $('.actionMapFrame').hide();
                $('.actionMapFrame[name*="' + key + '"]').show();
            }
            else {
                $('.actionMapFrame').show();
            }
        });
        $('.searchvalue').keyup(function() {
            delay(function() {
                $('.gosearch').trigger('click');
            }, 1000);
        });
    });
    function init() {
<?php
if ($searchaction):
?>
        $('.actionMapFrame').hide();
        $('.actionMapFrame[name="<?php echo $searchaction; ?>"]').show();
<?php
endif;
?>
    }
    var delay = (function() {
        var timer = 0;
        return function(callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();
</script>
<style>
    .amedit{
        display:none;
    }
</style>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Log Action Map
            </h1>
            <ol class="breadcrumb">
                <li>
                    <i class="fa fa-dashboard"></i>  <a href="/admin">Dashboard</a>
                </li>
                <li class="active">
                    <i class="fa fa-table"></i> Log Action Map
                </li>
            </ol>
        </div>
    </div>
    <!-- /.row -->
    <div class="form-group addActionFrame">
        <label>Create New Action : </label>
        <input type="text" name="action" id="action" placeholder="Enter New Action"/>
        <button type="button" class="btn btn-sm btn-success" id="addaction">Create</button>
    </div>
    <div class="form-group input-group">
        <input type="text" class="form-control searchvalue"/>
        <span class="input-group-btn"><button class="btn btn-default gosearch" type="button"><i class="fa fa-search"></i></button></span>
    </div>

    <div class="row">
        <?php foreach ($am_ary as $k => $v) { ?>
        <?php echo ($k % 2 == 0 ? '</div><div class="row">' : ''); ?>
        <div class="col-lg-6 actionMapFrame" name="<?php echo $v->action; ?>">
            <h2><?php echo $v->action; ?> <span class="amview"><button type="button" class="btn btn-sm btn-primary goamedit">Edit</button></span><span class="amedit"><button type="button" class="btn btn-sm btn-danger canceledit">Cancel</button>&nbsp;<button type="button" class="btn btn-sm btn-primary addkey">Add Key</button>&nbsp;<button type="button" class="btn btn-sm btn-primary goamsave">Save</button></span></h2>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Key<input type="hidden" name="oid" value="<?php echo $v->getID(); ?>"/></th>
                            <th>DataType</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($v->params as $key => $type) { ?>
                        <tr>
                            <td class="amkey">
                                <span class="amview"><?php echo $key; ?></span>
                                <span class="amedit">
                                    <button type="button" class="btn btn-xs btn-danger delkey">Del</button>
                                    <input type="text" name="key[]" value="<?php echo $key; ?>"/>
                                </span>
                            </td>
                            <td class="amtype">
                                <span class="amview"><?php echo $type; ?></span>
                                <span class="amedit">
                                    <select name="value[]">
                                        <?php foreach ($dataType_ary as $dataType) { ?>
                                        <option value="<?php echo $dataType; ?>" <?php echo ($dataType == $type ? 'selected="selected"' : ''); ?>><?php echo $dataType; ?></option>
                                        <?php } ?>
                                    </select>
                                </span>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php } ?>
    </div>
    <!-- /.row -->

</div>
<!-- /.container-fluid -->