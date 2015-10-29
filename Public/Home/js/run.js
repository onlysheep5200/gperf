$(document).ready(function(){
    
//    var list = $(".list");
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
           timer = setInterval(function(){
                $.getJSON(check_addr,function(data)
                    {
                    
                       // list.append(p.clone().text(data['result']));
                        if(data['complete']=='true')
                        {
                            clearInterval(timer);
                           // window.location.href=result_addr;
                           type = $(".result_tabs ul").find("li.current a").text().toLowerCase();
                           getProbeTaskStatus(type);

                        }
                    }); 
               },500);

        });

});
