$(document).ready(function(){
    
    var list = $(".list");
    var p = $("<p></p>");
    var timer;
    $.getJSON(check_addr,{arg:'init'},function(data)
        {
            if(data === 'no task')
            {
                alert("you haven't create task.");
                return;
            }
            else if (data === "failed to get task info")
            {
                alert (data);
                return;
            }
           for(x in data)
           {
                console.log(x);
                list.append(p.clone().text(data[x]+'...'));
           }
           timer = setInterval(function(){
                $.getJSON(check_addr,function(data)
                    {
                    
                        list.append(p.clone().text(data['result']));
                        if(data['complete']=='true')
                        {
                            clearInterval(timer);
                            list.append(p.clone().html('<b>Your task is finished. The results of your task will be show to you in 3 seconds</b>'));
                            setTimeout(function(){
                                    window.location.href=result_addr;
                                },3000);
                        }
                    }); 
               },500);

        });

});
