(function ($) {

    const requestUtil = function () {

    };

    requestUtil.prototype.setApiUrl = function (url) {
        return url;
    };

    requestUtil.prototype.setDataToken = function (data) {
        return data;
    };

    requestUtil.prototype.sendGetRequest = function (url, data, successCallback, errorCallback) {
        data = this.setDataToken(data);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: this.setApiUrl(url),
            type: "GET",
            dataType: "JSON",
            data: data,
            success: function (res) {
                if (typeof successCallback == 'function') {
                    if (res['code'] == 200) {
                        successCallback(res);
                    } else {
                        errorCallback(res);
                    }
                }
            },
            error: function (err) {
                console.error(err);
                if (typeof errorCallback == 'function') {
                    errorCallback(err);
                }
            },

        })

    };

    requestUtil.prototype.sendPostRequest = function (url, data, successCallback, errorCallback = function (error) {
    }) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: this.setApiUrl(url),
            type: "POST",
            dataType: "JSON",
            data: data,
            success: function (res) {
                if (typeof successCallback == 'function') {
                    if (res['code'] == 200) {
                        successCallback(res);
                    } else {
                        errorCallback(res);
                    }
                }
            },
            error: function (err) {
                console.error(err);
                if (typeof errorCallback == 'function') {
                    errorCallback(err);
                }
            },

        })

    };

    window.requestUtil = new requestUtil;
})(jQuery);
