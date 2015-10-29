$(document).ready(function(){

//    $('select[name="country"]').selectToAutocomplete();
    $('input').filter("[required='required']").each(function(){
            $(this).after("<span>*</span>")
        });
    $('input.needcheck').parent().append("<span class='info'></span>");
    $('input[name="repassword"]').parent().append("<span class='info'></span>");
    $('input.needcheck').keyup(function(){
            var addr = $(this).attr('ajxurl');
            (function(aim,addr){
               $.get(addr,{value : aim.val()},function(data){
                   var name = aim.attr('name');
                   var span = aim.parent().find('span.info');
                   if(data == 'true')
                   {
                        $(this).removeClass('false');
                        if(name=="username")
                        {
                            span.css('color','green').text('you can you the username');
                        }
                   }
                    else
                    {
                        $(this).addClass('false');
                        span.css('color','red');
                        if(name == "username")
                        {
                            if(aim.val()=='')
                                return;
                            span.css('color','red').text('the username has been used,please use another one');
                        }
                        else if('name' == 'password')
                        {
                           span.text('the length of password should be more than 4 characters and less than 20 characters');
                        }
                        else if ('name' == 'email')
                        {
                            span.text('invalid email address');
                        }
                    }
                   }); 
             })($(this),addr);
        });
    $('input[name="repassword"]').keyup(function(){
            
            if($(this).val() !== $('input[name="password"]').val())
            {
               $(this).parent().find('span.info').css('color','red').text('not equal to the password you input before'); 
               $(this).addClass('false');
            }
            else
            {
                $(this).parent().find('span.info').text('');
                $(this).removeClass('false');
            }
            });

    $(".submitbtn").click(function(e){
            if($(".false").length > 0)
            {
                alert("Some fields are illegal.Please correct them.");
            }
            else
            {
                var required = $("#register_form").find('[required="required"]');
                console.log(required.length);
                for(var x = 0;x<required.length;x++)
                {
                    if(required.eq(x).val())
                        continue;
                    else
                    {
                        alert(required.eq(x).attr('name')+' is empty');
                        return;
                    }
                }
                $(this).parent().parent().parent().submit();
            }

            })

})
