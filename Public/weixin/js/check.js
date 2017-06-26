//检查输入项
    function checkphone(){
        var phone = $("input[name='phone']").val();
        if (phone == "") {
            $(".weui_dialog_bd").html("请输入手机号");
            return false;
        } else if (!phone.match(/^(((1[3|5|7|8]{1}[0-9]{1}))+\d{8})$/)) {
            $(".weui_dialog_bd").html("手机号格式错误，请重新输入");
            return false;
        }
        return true;
    }
    //发送验证码
    function sendsms() {
        if(checkuser()) {
            $(".weui_dialog_bd").html("此手机号已注册，如果您忘记密码请修改密码！");
            $('#dialog2').show();
        } else {
            $.ajax({
                type: "post",
                url: '/interface.php?s=/home/Sms/smsSend',
                async: false,
                data: {mobile: $("#phone").val(),type:'注册'},
                dataType: "json",
                success: function(data){
                            console.log(data);
                    if (data.code == 2000){
                            time($("#sendcode"));
                            $(".weui_dialog_bd").html("验证码发送成功，可能会稍有延迟，请检查手机短信");
                        } else if(data.msg[0]){
                            $(".weui_dialog_bd").html(data.msg[0]);
                        } else{
                            $(".weui_dialog_bd").html("验证码发送失败，请稍候再试");
                        }
                        $('#dialog2').show();
                },
                error:function() {
                    return false;
                }
            });
        }

    }
    //按钮倒计时
    var wait=60;
    function time(o) {
        if (wait == 0) {
            o.removeClass('weui_btn_disabled');
            o.on({
                click:function(){
                    if (checkphone()) {
                        if (sendsms()){
                            time($("#sendcode"));
                            $(".weui_dialog_bd").html("验证码发送成功，可能会稍有延迟，请检查手机短信");
                            $('#dialog2').show();
                        } else {
                            $(".weui_dialog_bd").html("验证码发送失败，请稍候刷新页面再试");
                            $('#dialog2').show();
                        }
                    } else {
                        $('#dialog2').show();
                    }
                }
             });
            o.html("获取验证码");
            wait = 60;
        } else {
            $("#sendcode").off('click');
            o.addClass('weui_btn_disabled');
            o.html("重新发送(" + wait + ")");
            wait--;
            setTimeout(function() {
                    time(o)
                },
            1000)
        }
    }
    $(document).ready(function(){


            $('#primary2').on({
                click:function(){
                    $('#dialog2').hide();
                }
            });
            $("#sendcode").on({
                click:function(){
                    if (checkphone()) {
                        res = sendsms();
                    } else {
                        $('#dialog2').show();
                    }
                }
             });
        });

        window.hasuser =0;

        /**
         * 验证此手机号是否存在
         * @return {[type]} [description]
         */
        function checkuser()
        {
            var userstatus = 0;
             $.ajax({
                type: "post",
                async: false,
                url: '{{:U("Register/checkuser")}}',
                data: {mobile:$("input[name='phone']").val()},
                dataType: "json",
                success: function(data){
                    if(data == 1){
                        window.hasuser =1;
                        $(".weui_dialog_bd").html("此手机号已注册，如果您忘记密码请修改密码！");
                        $('#dialog2').show();
                    }else{
                        window.hasuser = 0;
                    }
                },
                error:function() {
                    return false;
                    $(".weui_dialog_bd").html("网络异常");
                    $('#dialog2').show();
                }
            });
             if (window.hasuser == 1){
                return true;
             } else {
                return false;
             }
        }

        /**
         * 检查验证码长度是否正确
         * @return {[type]} [description]
         */
        function checkcodetype(){
            var code = $("input[name='code']").val();
            if (code == "") {
                $(".weui_dialog_bd").html("请输入验证码");
                return false;
            } else if(isNaN(code) || code.length != 6) {
                $(".weui_dialog_bd").html("验证码格式错误");
                return false;
            }
            return true;
        }
        /**
         * 检查密码是否符合
         * @return {[type]} [description]
         */
        function checkpwd(){
            var pwd1 = $("input[name='password']").val();
            var pwd2 = $("input[name='primarypwd']").val();
            if (pwd1.length <6) {
                $(".weui_dialog_bd").html("密码最少6位");
                return false;
            } else if(pwd2 !==pwd1) {
                $(".weui_dialog_bd").html("两次密码输入不一致");
                return false;
            }
            return true;
        }

        /**
         * 检查验证码 、密码并提交
         * @return {[type]} [description]
         */
        function checkcode(){
                if (!checkuser() && checkphone() && checkcodetype()){
                    $.ajax({
                        type: "post",
                        url: '/interface.php?s=/home/Sms/checksms',
                        data: {mobile:$("input[name='phone']").val(),code:$("input[name='code']").val()},
                        dataType: "json",
                        success: function(data){
                            if(data == 1){
                                $('form').submit();
                            } else if (data == 0) {
                                $(".weui_dialog_bd").html("验证码错误");
                                $('#dialog2').show();
                            } else if (data.why == 2) {
                                $(".weui_dialog_bd").html("验证码超时");
                                $('#dialog2').show();
                            }
                        },
                        error:function() {
                            $(".weui_dialog_bd").html("网络异常");
                            $('#dialog2').show();
                        }
                    });
                } else {
                    $('#dialog2').show();
                }
        }