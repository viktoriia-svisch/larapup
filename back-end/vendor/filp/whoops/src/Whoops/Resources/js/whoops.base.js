Zepto(function($) {
  var $leftPanel      = $('.left-panel');
  var $frameContainer = $('.frames-container');
  var $appFramesTab   = $('#application-frames-tab');
  var $allFramesTab   = $('#all-frames-tab');
  var $container      = $('.details-container');
  var $activeLine     = $frameContainer.find('.frame.active');
  var $activeFrame    = $container.find('.frame-code.active');
  var $ajaxEditors    = $('.editor-link[data-ajax]');
  var $header         = $('header');
  $header.on('mouseenter', function () {
    if ($header.find('.exception').height() >= 145) {
      $header.addClass('header-expand');
    }
  });
  $header.on('mouseleave', function () {
    $header.removeClass('header-expand');
  });
  var renderCurrentCodeblock = function(id) {
    $('.code-block').removeClass('prettyprint');
    if (typeof(id) === 'undefined' || typeof(id) === 'object') {
      var id = /frame\-line\-([\d]*)/.exec($activeLine.attr('id'))[1];
    }
    $('#frame-code-linenums-' + id).addClass('prettyprint');
    $('#frame-code-args-' + id).addClass('prettyprint');
    prettyPrint(highlightCurrentLine);
  }
  var highlightCurrentLine = function() {
    var activeLineNumber = +($activeLine.find('.frame-line').text());
    var $lines           = $activeFrame.find('.linenums li');
    var firstLine        = +($lines.first().val());
    $activeFrame.find('.code-block').first().css({
      maxHeight: 345,
      overflow: 'hidden',
    });
    var $offset = $($lines[activeLineNumber - firstLine - 10]);
    if ($offset.length > 0) {
      $offset[0].scrollIntoView();
    }
    $($lines[activeLineNumber - firstLine - 1]).addClass('current');
    $($lines[activeLineNumber - firstLine]).addClass('current active');
    $($lines[activeLineNumber - firstLine + 1]).addClass('current');
    $container.scrollTop(0);
  }
  $frameContainer.on('click', '.frame', function() {
    var $this  = $(this);
    var id     = /frame\-line\-([\d]*)/.exec($this.attr('id'))[1];
    var $codeFrame = $('#frame-code-' + id);
    if ($codeFrame) {
      $activeLine.removeClass('active');
      $activeFrame.removeClass('active');
      $this.addClass('active');
      $codeFrame.addClass('active');
      $activeLine  = $this;
      $activeFrame = $codeFrame;
      renderCurrentCodeblock(id);
    }
  });
  var clipboard = new Clipboard('.clipboard');
  var showTooltip = function(elem, msg) {
    elem.setAttribute('class', 'clipboard tooltipped tooltipped-s');
    elem.setAttribute('aria-label', msg);
  };
  clipboard.on('success', function(e) {
      e.clearSelection();
      showTooltip(e.trigger, 'Copied!');
  });
  clipboard.on('error', function(e) {
      showTooltip(e.trigger, fallbackMessage(e.action));
  });
  var btn = document.querySelector('.clipboard');
  btn.addEventListener('mouseleave', function(e) {
    e.currentTarget.setAttribute('class', 'clipboard');
    e.currentTarget.removeAttribute('aria-label');
  });
  function fallbackMessage(action) {
    var actionMsg = '';
    var actionKey = (action === 'cut' ? 'X' : 'C');
    if (/Mac/i.test(navigator.userAgent)) {
        actionMsg = 'Press ⌘-' + actionKey + ' to ' + action;
    } else {
        actionMsg = 'Press Ctrl-' + actionKey + ' to ' + action;
    }
    return actionMsg;
  }
  function scrollIntoView($node, $parent) {
    var nodeOffset = $node.offset();
    var nodeTop = nodeOffset.top;
    var nodeBottom = nodeTop + nodeOffset.height;
    var parentScrollTop = $parent.scrollTop();
    var parentHeight = $parent.height();
    if (nodeTop < 0) {
      $parent.scrollTop(parentScrollTop + nodeTop);
    } else if (nodeBottom > parentHeight) {
      $parent.scrollTop(parentScrollTop + nodeBottom - parentHeight);
    }
  }
  $(document).on('keydown', function(e) {
    var applicationFrames = $frameContainer.hasClass('frames-container-application'),
        frameClass = applicationFrames ? '.frame.frame-application' : '.frame';
	  if(e.ctrlKey || e.which === 74  || e.which === 75) {
		  if (e.which === 38  || e.which === 75 ) {
			  $activeLine.prev(frameClass).click();
			  scrollIntoView($activeLine, $leftPanel);
			  $container.focus();
			  e.preventDefault();
		  } else if (e.which === 40  || e.which === 74 ) {
			  $activeLine.next(frameClass).click();
			  scrollIntoView($activeLine, $leftPanel);
			  $container.focus();
			  e.preventDefault();
		  }
	  } else if (e.which == 78 ) {
      if ($appFramesTab.length) {
        setActiveFramesTab($('.frames-tab:not(.frames-tab-active)'));
      }
    }
  });
  renderCurrentCodeblock();
  $ajaxEditors.on('click', function(e){
    e.preventDefault();
    $.get(this.href);
  });
  $('.sf-dump-expanded')
    .removeClass('sf-dump-expanded')
    .addClass('sf-dump-compact');
  $('.sf-dump-toggle span').html('&#9654;');
  function setActiveFramesTab($tab) {
    $tab.addClass('frames-tab-active');
    if ($tab.attr('id') == 'application-frames-tab') {
      $frameContainer.addClass('frames-container-application');
      $allFramesTab.removeClass('frames-tab-active');
    } else {
      $frameContainer.removeClass('frames-container-application');
      $appFramesTab.removeClass('frames-tab-active');
    }
  }
  $('a.frames-tab').on('click', function(e) {
    e.preventDefault();
    setActiveFramesTab($(this));
  });
});
