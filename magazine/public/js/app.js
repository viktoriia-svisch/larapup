let sectionMessage = $("body");
function flashMessage(message, isWarning, duration) {
    isWarning = !!isWarning;
    if (!!duration || duration < 2000) {
        duration = 2000;
    }
    let NotiType = isWarning ? " bg-danger" : " bg-success";
    let clType = "flashMessage card text-white" + NotiType;
    let warningContainer = $("<div/>").addClass(clType);
    let warningBody = $("<div/>").addClass("card-body").text(message);
    warningContainer.append(warningBody);
    warningContainer.hide();
    sectionMessage.append(warningContainer);
    warningContainer.fadeIn("fast");
    setTimeout(function () {
        warningContainer.fadeOut("slow", function () {
            this.remove();
        });
    }, duration)
}
