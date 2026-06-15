function timeto12(date)
{
    const rdate = date.replace(" ", "T");
    const time12hr = new Date(rdate).toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit",
    hour12: true
    });
    return time12hr;
}

function compareTime(date, nowtime)
{
    const rdate = date.replace(" ", "T");
    const ntime = nowtime;
    if(rdate < ntime)
    {
        console.log("last time is old");
    } else {
    }
}

function updateLastseen()
{
    fetch("lastseen.php");
    return;
}