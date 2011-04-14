function showdiv(id)
{
    for (i = 0; i < link.length; i++) 
    {
        link[i].className = "btnOFF";
        data[i].style.display = "none";
    }
    id.className = "BtnON";
    for (i = 0; i < link.length; i++) if (link[i].className == "BtnON") break;
    data[i].style.display = "block";
    ImgSize(data[i]);
}
function ImgSize(id) 
{
    var def = 75;
    var img = id.getElementsByTagName('img');
    for (i = 0; i < img.length; i++) 
    {
        var Max = (img[i].width > img[i].height) ? img[i].width : img[i].height;
        var per = 1;
        if (Max > def) per = def / Max;
        img[i].width *= per;
    }
}
