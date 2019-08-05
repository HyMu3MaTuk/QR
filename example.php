<html>
<head>
<title> Тестируем QR код </title>
</head>
    
    <body>
<?php
        echo "hello world";
?>
<script src="/vue-qrcode-reader/dist/vue-qrcode-reader.browser.js"></script>
<link href="/vue-qrcode-reader/dist/vue-qrcode-reader.css" rel="stylesheet" />

<div id="qr_code_app" class="row">
    <div class="qrcode_header">Поднесите чек к камере вашего устройства</div>
    <img class="qrcode_img" src="/index.png" />
    <div><qrcode-stream @decode="onDecode" @init="onInit"></qrcode-stream></div>
    <div class="qrcode_text1">Если у вас не получается отсканировать QR-код<br> попробуйте сделать то же самое с другого устройства</div>
    <a href="/vue-qrcode-reader/src/misc/errors.js"><div class="qrcode_helplink">Подробнее о проблемах со сканером</div></a>
    
	<div id="service_modal" class="reveal-modal qr_error_win" data-reveal aria-labelledby="modalTitle" aria-hidden="true" role="dialog">
        <a class="close-reveal-modal" aria-label="Close">&#215;</a>
        <img class="qr_err_icon" src="/error_icon.png">
        <div id="service_content">ОШИБКА</div>
    </div>
	
</div>

<script>
const url = "/api/offline_receipt";
    $('#auth_modal3').foundation('reveal','open');
Vue.use(VueQrcodeReader);

new Vue({
    el: '#qr_code_app',

    data () {
        return {
            decodedContent: '',
            message: ''           
        };
    },
    methods: {
        onDecode (content) {
            this.decodedContent = content;
            let that = this;           
            $.ajax({
                url: url,
                type: "POST",
                data: {"qr_code" : content},
                dataType: 'json',
                success: function(response){
                    console.log(response);
                    
                    that.message = response.description;
                    $("#service_content").removeClass("error");
                    $("#service_content").html("ПОЗДРАВЛЯЕМ");
                    $(".qr_err_icon").hide();
                    $("#service_modal").foundation('reveal','open');
                },
                error: function(error){
                    console.log(error);
                    let html = error.responseJSON.description;
                    if (error.status === 401) {
                        $("#auth_modal3").foundation('reveal','open');
                    } else {                     
                        that.message = html;
                        $(".qr_err_icon").show();
                        $("#service_content").html("ОШИБКА");
                        $("#service_content").addClass("error");
                        $("#service_modal").foundation('reveal','open');
                    }
                }
            });     
        },

        onInit (promise) {
            promise.then(() => {
                console.log('Successfully initilized! Ready for scanning now!');
                $(".qrcode_img").hide();
                $(".qrcode-stream").show();
            })
            .catch(error => {
                $(".qrcode_img").show();
                $(".qrcode-stream").hide();
                if (error.name === 'NotAllowedError') {
                    this.message = 'Пожалуйста, предоставьте доступ к камере вашего устройства'
                } else if (error.name === 'NotFoundError') {
                    this.message = 'На вашем устройстве есть камера?'
                } else if (error.name === 'NotSupportedError') {
                    this.message = 'Страница не защищена (HTTPS, localhost or file://)'
                } else if (error.name === 'NotReadableError') {
                    this.message = 'Невозможно получить доступ к вашей камере. Она уже используется?'
                } else if (error.name === 'OverconstrainedError') {
                    this.message = 'Кажется вы выбрали неработающую камеру'
                } else {
                    this.message = 'UNKNOWN ERROR: ' + error.message
                }
                $(".qr_err_icon").show();
                $("#service_content").html("ОШИБКА");
                $("#service_content").addClass("error");
                $("#service_modal").foundation('reveal','open');
            })
        }
    }
});
$(".qrcode_er_button1").click(function(){
    $("#service_modal").foundation("reveal","close");
});
</script>
    </body>
</html>
