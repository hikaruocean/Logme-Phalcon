function ajaxElement(obj, page,type) {
    var p_str = '';
    var fre = '';
    type = type || 'json';
    obj.find('input[type="hidden"]').each(function() {
        if (p_str.length !== 0)
            p_str += '&';
        p_str += $(this).attr("name") + '=' + $(this).val();
    });
    obj.find('input[type="password"]').each(function() {
        if (p_str.length !== 0)
            p_str += '&';
        p_str += $(this).attr("name") + '=' + $(this).val();
    });
    obj.find('input[type="text"]').each(function() {
        if (p_str.length !== 0)
            p_str += '&';
        p_str += $(this).attr("name") + '=' + $(this).val();
    });
    obj.find('input[type="checkbox"]:checked').each(function() {
        if (p_str.length !== 0)
            p_str += '&';
        p_str += $(this).attr("name") + '=' + $(this).val();
    });
    obj.find('input[type="radio"]:checked').each(function() {
        if (p_str.length !== 0)
            p_str += '&';
        p_str += $(this).attr("name") + '=' + $(this).val();
    });
    obj.find('select').each(function() {
        if (p_str.length !== 0)
            p_str += '&';
        p_str += $(this).attr("name") + '=' + $(this).val();
    });
    obj.find('textarea').each(function() {
        if (p_str.length !== 0)
            p_str += '&';
        p_str += $(this).attr("name") + '=' + $(this).val();
    });
    
    $.ajax({
        url: page,
        type: 'POST',
        async: false, //為什麼這邊要設同步，因為function跑完return時 ajax的值還來不及回來，必須要等到ajax做完再去做return，超爽der
        dataType: type,
        data: p_str,
        error: function(er) {
            alert(er);
        },
        success: function(re) {
            fre = re;
        }
    });
    return fre;
}