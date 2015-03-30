/**
 *  Logme JS API
 *  author Hikaru
 */
var LogmeJS = function(url) {
    this.api = 'JS';
    this.url = url || '';
};

LogmeJS.prototype.setUrl = function(url) {
    this.url = url;
    return true;
};
LogmeJS.prototype.geturl = function() {
    return this.url;
};

LogmeJS.prototype.sendLogJS = function(obj) {
    for (var i in obj) {
        if (obj[i] instanceof Date) {
            obj[i] = Math.floor(obj[i].getTime() / 1000);
        }
    }
    $.ajax({
        url: this.url,
        type: 'POST',
        dataType: 'JSON',
        data: obj,
        success: function(json) {
            if (json.result === '200') {
                console.log('LogmeJS sendLogJS OK');
            }
            else {
                console.log('LogmeJS error [' + json.result + ',' + json.msg + ']');
            }
        }
    });
};