jQuery(function($){
    $( "#lesson_datepicker" ).datepicker();
 
});

jQuery( function($) {
    $( "#dialog" ).dialog({
        autoOpen: false,
        modal: true,
        buttons: {
            Ok: function() {
                $( this ).dialog( "close" );
            }
        }
    });
});

jQuery( function($) {
    $('tbody tr td[data-id]').addClass('clickable').click( function() {
        var id =  $(this).attr('data-id');
        var path = $(this).attr('data-path');
        lesson_scheduler_detail_dialog($,id,path);

    }).find('a').hover( function() {
        $(this).parents('tr').unbind('click');
    }, function() {
        $(this).parents('tr').click( function() {
        });
    });
});

jQuery( function($) {
    $('div .lesson_scheduler_mobile').click( function() {
        var id =  $(this).attr('data-id');
        var path = $(this).attr('data-path');
        lesson_scheduler_detail_dialog($,id,path);
    });
    
});

function lesson_scheduler_detail_dialog($,id,path){
        var posturl = path+"/wp-admin/admin-ajax.php";

        $.ajax({ 
            async: false,
            data: {"action":"get_lesson_detail","data-id": id},
            url: posturl,
            type:'POST',
            dataType: 'json',
            success: function(data) {
                
                var str = "<p>練習場所:"+data['lesson_place']+"</p>";
                str = str + "<p>練習日:"+data['lesson_date']+"</p>";
                str = str + "<p>練習時間:"+data['lesson_time']+"</p>";
                if( typeof data['lesson_desc'] !== 'undefined' ){
                    str = str + "<p>備考:"+data['lesson_desc']+"</p>";
                }
                str = str + "<div class='lesson_scheduler_table'>";
                str = str + "<table><thead><tr><td>名前</td><td>出欠</td><td>コメント</td></tr></thead>";
                var user_status = data['user_status'];
                for( var key in user_status){
                    str = str + "<tbody><tr><td>" + key +"さん" +"</td>";
                    str = str + "<td>" + user_status[key]['status'] + "</td>";
                    str = str + "<td>" + user_status[key]['comment'] + "</td>";
                    str = str + "</tr>";
                }
                str = str + "</tbody></table>";
                str = str + "</div>";
               
                $("#lesson_scheduler_dialog").children().remove();
                $("#lesson_scheduler_dialog").append(str);
                
                $("#dialog").dialog("open");
                
            },
            error:  function(XMLHttpRequest, textStatus, errorThrown) {
                alert("ng:"+textStatus);
            }
        });
    
}