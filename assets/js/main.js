jQuery(document).ready(function($){
    let w_width = $(window).width();
    let w_height= 800;
    let popup_width  = 768;
    let w_center_x = ( w_width - popup_width ) / 2;
    let w_center_y = 100;
    let popup_height    = w_height - w_center_y;
    
    let channel_name    = $('input#ss_submit_change').val();
    let $socialshop_page = $('#socialshop-page');
    var pusher = new Pusher( pusher_key , {
        cluster: pusher_cluster
    });
    Pusher.logToConsole = true;
    console.log('pusher',pusher);
    var channel = pusher.subscribe( channel_name );
    channel.bind( pusher_event_name , (res) => {
        $socialshop_page.addClass('loading-page');
        let code = res.data && res.data.code ? res.data.code : '';
        if( code ){
            verify_code(code);
        }
    });

    function verify_code(code){
        console.log('verify_code',code);
        let _wpnonce = $socialshop_page.find('input[name="_wpnonce"]').val();
        let _wp_http_referer = $socialshop_page.find('input[name="_wpnonce"]').val();
        $.ajax({
            type     : 'POST',
            dataType : 'json',
            data : {
                'action' : 'socialshop_verify_code',
                'code'   : code,
                '_wpnonce' : _wpnonce,
                '_wp_http_referer' : _wp_http_referer
            },
            url : socialshop_admin_ajax,
            beforeSend: function(){
                $socialshop_page.find('.notice').remove();
            },
            success : function (data){
                console.log('data',data);
                if( data.success == false ){
                    let message_response = data.data.message ? data.data.message : 'Something went wrong!';
                    $socialshop_page.prepend( alertError( message_response ) )
                }else{
                    location.reload();
                }
            },
            error:function(xhr, status, error){
                console.log(xhr);
                console.log(status);
                console.log(error);
            },
            complete: function(){
                $socialshop_page.removeClass('loading-page');
            }
        });
    }

    $('#ssLogin').click(function(e){
        e.preventDefault()
        let link_to = $(this).data('url');
        let new_window = window.open( link_to ,
            'socialhead',
            `height=${popup_height},width=768,resizable=yes,top=${w_center_y},left=${w_center_x}`);

        $socialshop_page.addClass('loading-page');

        if (window.focus)
            new_window.focus();


    })

    return false;

});

function alert(type, message){
    return `<div class="notice notice-${type}  my-3">
                    <p> <b> Socialshop Error:</b> ${message} </p>
                </div>`;
}
function alertError(message){
    return alert('error', message )
}
function alertSuccess(message){
    return alert('success', message )
}
function alertWarning(message){
    return alert('warning', message )
}
function alertInfo(message){
    return alert('info', message )
}

function generateChannelId(){
    var text = function () {
        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1)
    }
    return (
        text() +
        text() +
        '' +
        text() +
        '' +
        text() +
        '' +
        text() +
        '' +
        text() +
        text() +
        text()
    )
}