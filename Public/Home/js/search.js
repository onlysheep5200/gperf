$(function() {
    var category = $(
               " <tr>"+
                    '<td class="td1"><input type="checkbox" class="checkbox" /><span class="checkboxspan checkboxspan_current"></span></td>'+
                    '<td class="td2"><span class="span"> {$vo["arg"]}</span></td>'+
                    '<td class="td3"></td>'+
                    '<td class="td4"> </td>'+
                    '<td class="td5"> </td>'+
                    '<td class="td6"> </td>'+
                   '<td class="td7"> </td>'+
                    '<td class="td8"> </td>'+
                "</tr>"
    );

    var item = $(
               " <tr>"+
                    '<td class="td1"><input type="checkbox" class="checkbox" /><span class="checkboxspan checkboxspan_current"></span></td>'+
                    '<td class="td2"><span class="span"> {$vo["arg"]}</span></td>'+
                    '<td class="td3"></td>'+
                    '<td class="td4"> </td>'+
                    '<td class="td5"> </td>'+
                    '<td class="td6"> </td>'+
                   '<td class="td7"> </td>'+
                    '<td class="td8"> </td>'+
                "</tr>"
    );
    var selectedProbes = [];

    $("#tree").treeview({
        animated: "fast",
        collapsed: true,
        unique: false,
        persist: "cookie",
        toggle: function() {
        }
    });
    $('.checkboxspan').click(function()
    {
        console.log('change triggered');
        var result = '';
        selectedProbes = [];
        $('div.table tr.detail input[name="pid"]:checked').each(function(){
            result += $(this).val()+' ';    
            selectedProbes.push($(this).val());
        });
        $.get(search_addr,{pids:result});
    });
    
    function getProbesByOrg(org,parent)
    {
        (function(parent){
        $.getJSON(treeitems_addr,{org:org},function(data){
            for(var x in data)
            { 
                var li = $('<li class="leaf"><a href="#">'+data[x]['name']+'</a>'+'</li>');
                li.find('a').attr('data-city',data[x]['city']).attr('data-pid',data[x]['pid']).attr('data-country',data[x]['country']).attr('data-name',data[x]['name']).attr('data-org',data[x]['organization']).attr('data-mid',data[x]['mid']).attr('data-position',data[x]['position']);
                if(data[x]['checked'])
                {
                    li.find("a").addClass('current');
                }
                li.find('a').click(leafListener);
                parent.find('ul').append(li);
            }
        })})(parent);
    }

    $(".level2 span,.level2 div").click(function(){
        var level = $(this).parent();
        var ul = level;
        if(ul.find('li').length > 0)
            return;
        ul.find("ul").html('');
        var org = level.find('span').eq(0).text();
        getProbesByOrg(org,level);
    });

    $("#savebtn").click(function(){
        $('#mapflow').hide();
        $('#fullcontent').hide();
        var selectedItems = $('tr.detail .td2 .checkboxspan');
        console.log(selectedItems.length);
        var showed = [];
        var addingName = [];
        selectedItems.each(function(){
           var input =  $(this).parent().find('input[name="pid"]')    
           if($.inArray(input.val(),selectedProbes)!=-1)
           {
              $(this).addClass('checkboxspan_current');
              input.attr('checked','checked');
              showed.push(input.val());
           }
           else
           {
              $(this).removeClass('checkboxspan_current');
              input.attr('checked',null);
           }
        });
        
        showed.splice(0,showed.length);


    });
    function leafListener(e)
    {
        e.preventDefault();
        $(this).toggleClass('current');
        var name = $(this).text();
        var pid = $(this).attr('data-pid');
        var position = $(this).attr('data-position');
        if($(this).hasClass('current'))
        {
            if($.inArray(pid,selectedProbes)==-1)
            {
                selectedProbes.push(pid);
            }
            if(position!=null && position!='')
            {
                var position = position.split(',');
                position[0] = parseFloat(position[0]);
                position[1] = parseFloat(position[1]);
                var latlng = new Microsoft.Maps.Location(position[0],position[1]);
                map.setView({zoom:8,center:latlng});
                var length = map.entities.getLength();
                var marker = new Microsoft.Maps.Pushpin(latlng);
                map.entities.push(marker);
                markers[name] = length;
            }
        }
        else
        {
            var index = $.inArray(pid,selectedProbes);
            if(index != -1)
            {
                selectedProbes.splice(index,1);
            }
            if(markers[name]>=0)
            {
                var removeIndex = markers[name];
                map.entities.removeAt(markers[name]);
                markers[name] = null;
                for(var x in markers)
                {
                    if(markers[x] >removeIndex)
                    {
                        markers[x]-=1;
                    }
                }
                map.setView({center:new Microsoft.Maps.Location(39.915,116.408),zoom:2});
            }
        }
    }
/*
    function initLeafListener()
    {
        $("li.leaf,li.leaf a").click(function(e){
            e.preventDefault();
            console.log("leaf is clicked");
            if($(this).attr('href') && $(this).attr('href')!='')
            {
                $(this).toggleClass('current');
            }
            else
            {
                $(this).find('a').toggleClass('current');
            }
        })
    }
*/
})
