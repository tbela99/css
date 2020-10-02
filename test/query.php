#!/usr/bin/php
<?php

use TBela\CSS\Compiler;
use TBela\CSS\Parser as CssParser;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Query\Parser as QueryParser;

require 'autoload.php';

$css = '[data-catalyst], a {

    position: absolute;
}';

var_dump(array_map('trim', (new CssParser($css))->parse()->query('[data-catalyst]')));
die;

echo (new QueryParser())->parse('html|body|.min-width-lg|.bg-blue|.text-white|.Progress|
.progress-pjax-loader|.Progress-item|
.progress-pjax-loader > .progress-pjax-loader-bar|.Header|.mb-n1|.Header-item--full|
.header-search-current.header-search|.header-search-current .header-search-wrapper|
.header-search-current .header-search-input|.input-sm|.header-search-current .jump-to-suggestions|
.m-0|.flex-justify-start|.header-search-current .jump-to-suggestions-results-container .navigation-item|
.p-2|.header-search-current .jump-to-suggestions-path|.text-center|
.header-search-current .jump-to-suggestions-path .jump-to-octicon|
.no-wrap|.header-search-current .jump-to-suggestions-path .jump-to-suggestion-name|
.text-gray-light|.pl-1, .px-1|.px-1|.header-search-current .jump-to-suggestions-results-container .d-on-nav-focus|
.ml-1|.m-2|.mr-3|.mt-n3|.mb-n3|.py-3|.notification-indicator|.notification-indicator .mail-status|
.dropdown-header|.mr-0|.Header, .Header-item|.Header-item|.Header-link|
.avatar|.avatar-user|.header-nav-current-user|.mt-n1|.mb-n2|.pt-2|
.header-nav-current-user .user-profile-link|b, strong|.header-nav-current-user .css-truncate-target|
.pr-3|.user-status-container, .user-status-container .team-mention, .user-status-container .user-mention|
.user-status-container|.border|.py-1|.pl-2, .px-2|.px-2|.btn-block|.circle|.flex-justify-center|
.flex-shrink-0|.v-align-bottom|.lh-condensed-ultra|.min-width-0|.user-status-message-wrapper|
.css-truncate.css-truncate-overflow, .css-truncate .css-truncate-overflow, .css-truncate.css-truncate-target, 
.css-truncate .css-truncate-target|.css-truncate.css-truncate-target, .css-truncate .css-truncate-target|
.width-fit|.user-status-message-wrapper div|.text-gray|.anim-fade-in|.anim-fade-in.fast|
details-dialog|.Box--overlay|.rounded-1|.flex-auto|.Box-header|.bg-gray|.Box--overlay .Box-header|
.Box-btn-octicon.btn-octicon|.btn-octicon|h3|.Box-title|.py-2|.form-group|.input-group|
.d-table|.input-group-button|.btn-outline|.input-group-button:first-child .btn|.p-0|
.user-status-container .input-group-button .btn|.form-group .form-control|.input-group .form-control|
.input-group-button, .input-group .form-control|
.input-group-button:first-child .btn, .input-group .form-control:first-child|
.input-group-button:last-child .btn, .input-group .form-control:last-child|.d-table-cell|
.form-group .error, .form-group .indicator, .form-group .success|
.my-1|.text-small|.label-characters-remaining|.mr-n3|.ml-n3|.pl-3, .px-3|.px-3|.overflow-hidden|
.user-status-suggestions|.user-status-suggestions.collapsed|h3, h4|h4|.my-3|.text-normal|.mx-3|.col-6|
.float-left|.link-gray|.flex-items-baseline|.flex-items-stretch|.emoji-status-width|g-emoji|.text-left|
.no-underline|.user-status-limited-availability-container|.form-checkbox|.my-0|
[type="checkbox"], [type="radio"]|.form-checkbox input[type="checkbox"], .form-checkbox input[type="radio"]|
label|.text-gray-dark|.d-block|.form-checkbox .note|.note|.pb-2|.pt-3|.f5|
.dropdown|.v-align-baseline|.btn .dropdown-caret|.dropdown-caret|.overflow-auto|.pl-0, .px-0|
.mb-1|.text-bold|.lh-condensed|.ws-normal|.border-top|.flex-justify-between|.flex-items-center|.p-3|
.d-flex|.btn-primary.disabled, .btn-primary:disabled, .btn-primary[aria-disabled="true"]|.mr-2|
.btn.disabled, .btn:disabled, .btn[aria-disabled="true"]|.dropdown-divider|.position-relative|
.feature-preview-indicator|.feature-preview-details .feature-preview-indicator|
.dropdown-item.btn-link, .dropdown-signout|.dropdown-signout|.border-bottom|.mb-0|.pb-4|
.shelf|.intro-shelf|.shelf .container|.mx-auto|.shelf-content|h1, h2, h3, h4, h5, h6|h1, h2|
h2|.shelf-title|p|.shelf-lead|.intro-shelf .shelf-lead|.btn-primary|.shelf-cta|.shelf-dismiss|
.close-button|.tooltipped|.mr-1|.shelf-dismiss .close-button|.v-align-text-top|.mt-5|
.select-menu-list|
.select-menu-item.selected, details-menu .select-menu-item[aria-checked="true"], 
details-menu .select-menu-item[aria-selected="true"]|
.select-menu-item.selected > .octicon, details-menu .select-menu-item[aria-checked="true"] > .octicon, details-menu 
.select-menu-item[aria-selected="true"] > .octicon|.select-menu-item.selected .octicon-check, 
.select-menu-item.selected .octicon-circle-slash, details-menu .select-menu-item[aria-checked="true"] .octicon-check, 
details-menu .select-menu-item[aria-checked="true"] .octicon-circle-slash, 
details-menu .select-menu-item[aria-selected="true"] .octicon-check, 
details-menu .select-menu-item[aria-selected="true"] .octicon-circle-slash|
.select-menu-item.selected .description, details-menu .select-menu-item[aria-checked="true"] .description, 
details-menu .select-menu-item[aria-selected="true"] .description|.v-align-text-bottom|.width-full|
.select-menu-item.last-visible, .select-menu-list:last-child .select-menu-item:last-child|
.select-menu-item|.select-menu-item .octicon-check, .select-menu-item .octicon-circle-slash, 
.select-menu-item input[type="radio"]:not(:checked) + .octicon-check, 
.select-menu-item input[type="radio"]:not(:checked) + .octicon-circle-slash|
.select-menu-item-icon|.select-menu-item-text|.select-menu-item-heading|.select-menu-item-text .description|
.select-menu-item .hidden-select-button-text|.select-menu-item .octicon|
.starring-container.on .unstarred, .starring-container .starred|
.btn-sm|.btn-with-count|.btn-sm .octicon|.social-count|.UnderlineNav .Counter|
.Counter|.hx_underlinenav-item .Counter|.Counter:empty|.dropdown-menu|
.dropdown-menu-sw|ol, ul|.dropdown-menu > ul|.dropdown-item|.table-list-triage|
.table-list-header-meta|.float-right|img|.v-align-middle|.text-red|.f6|
article, aside, details, figcaption, figure, footer, header, main, menu, nav, section|
.d-inline-block|.table-list-header-toggle .select-menu|summary|details summary|
.details-reset > summary|.table-list-header .btn-link|.table-list-header-toggle .btn-link|
.table-list-header-toggle .select-menu-button|details:not([open]) > :not(summary)|
.Box .section-focus .edit-section, [data-catalyst], 
auto-complete, details-dialog, 
details-menu, file-attachment, filter-input, image-crop, in-viewport, include-fragment, 
poll-include-fragment, remote-input, tab-container, text-expander|.select-menu-modal|
.select-menu-divider, .select-menu-header|.select-menu-divider, .select-menu-header .select-menu-title|
.select-menu-filters|.select-menu-filters, .select-menu-header|
.select-menu-text-filter|.select-menu-text-filter:first-child:last-child|.form-control, .form-select|
.select-menu-text-filter input|.position-fixed|.right-0|.d-none|.flash-full|.issue-reorder-warning|
.container|.btn|.btn-link|.top-0|.left-0|.mt-2|.ml-2|.show-on-focus, .sr-only|
.show-on-focus|.repo-health.is-loading .repo-health-results, 
.sortable-button-item:first-of-type .sortable-button[data-direction="up"], 
.sortable-button-item:last-of-type .sortable-button[data-direction="down"]|.btn .octicon|
.btn .octicon:only-child|.flash-error|.ajax-error-message|.ajax-error-message > .octicon-alert|button, input|
button, select|[type="reset"], [type="submit"], button, html [type="button"]|button, input, select, textarea|
button|.flash-close|.flash-close .octicon|.flash-error .octicon|.flash|.flash-warn|
.flash-banner|svg:not(:root)|.octicon|.flash .octicon|.flash-warn .octicon|[hidden][hidden]|[hidden]|
a|[hidden], template|.position-absolute|.Popover|*|.Box|.box-shadow-large|
.Popover-message|.Popover-message--bottom-left, .Popover-message--top-left');
die;

//$property = new PropertyList();
//
//$property->set('border-radius', '10% 17% 10% 17% / 50% 20% 50% 20%'); //, 'border-radius: 10% 17%/50% 20%'];
//echo $property;
//die;

//$query = 'span[@name="foo"] [@name="bar"]';
//$css = file_get_contents(__DIR__.'/query/style.css');
//
//$parser = new Parser();
//
//echo $parser->parse($query)."\n";
// var_dump($parser->parse($query));

echo (\TBela\CSS\Value::parse('.el {
  margin: 10px calc(2vw + 5px);
  border-radius: 15px calc(15px/3) 4px 2px;
  transition: transform calc(1s - 120ms);
}

.el {
  /* Nope! */
  counter-reset: calc("My " + "counter");
}
.el::before {
  /* Nope! */
  content: calc("Candyman " * 3);
}
.el {
  width: calc(
    100%     /   3
  );
}

.el {
  width: calc(
    calc(100% / 3)
    -
    calc(1rem * 2)
  );
}
.el {
  width: calc(
   (100% / 3)
    -
   (1rem * 2)
  );
}
.el {
  width: calc(100% / 3 - 1rem * 2);
}
.el {
  /* This */
  width: calc(100% + 2rem / 2);

  /* Is very different from this */
  width: calc((100% + 2rem) / 2);
}
@media (min-width: calc(40rem + 1px)) {
  /* Wider than 40rem */
  transform: rotate(calc(1turn + 45deg));

animation-delay: calc(1s + 15ms);
}

'));
die;

//
//

$css = '@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}

body {
  background-color: green;
  color: #fff;
  font-family: Arial, Helvetica, sans-serif;
}
h1 {
  color: #fff;
  font-size: 50px;
  font-family: Arial, Helvetica, sans-serif;
  font-weight: bold;
}

@media print, screen and (max-width: 12450px) {

p {
      color: #f0f0f0;
      background-color: #030303;
  }
}

@media print {
  @font-face {
    font-family: MaHelvetica;
    src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
      url(MgOpenModernaBold.ttf);
    font-weight: bold;
  }
  body {
    font-family: "Bitstream Vera Serif Bold", serif;
  }
  p {
    font-size: 12px;
    color: #000;
    text-align: left;
  }

  @font-face {
    font-family: Arial, MaHelvetica;
    src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
      ;
    font-weight: bold;
  }
}';

$query = '.select-menu-item .octicon-check, .select-menu-item .octicon-circle-slash, .select-menu-item input[type="radio"]:not(:checked) + .octicon-check, .select-menu-item input[type="radio"]:not(:checked) + .octicon-circle-slash';
//$query = '.select-menu-item .octicon-check, .select-menu-item .octicon-circle-slash';
//$query = '.select-menu-item .octicon-check';
//$query = ' . / [ @value = "print" ] ';
//$query = ' [ contains( @name , "background" ) ]';
//$query = ' [ not( color( @value , "white") ) ] ';
//$query = ' [ equals( @name , "color" ) ] ';
//$query = ' [ beginswith( @name , "color" ) ] ';
//$query = '.select-menu-item
//.octicon-check';
//$query = '.select-menu-item
//.octicon-check | [ beginswith( @name , "color" ) ] ';
//$query = '// @font-face / src / ..';
//$query = '@media[@value=print],p';
//$query = '// @font-face / src / .. | @media[@value^=print][1],p';
//$query = '//* / color/ ..';
//$query = '[@name]';
//$query = '@font-face/src/..|html, body|html|body|.min-width-lg|.bg-blue|.text-white|.Progress|.progress-pjax-loader|.Progress-item|.progress-pjax-loader \> .progress-pjax-loader-bar|.Header|.mb-n1|.Header-item--full|.header-search-current.header-search|.header-search-current .header-search-wrapper|.header-search-current .header-search-input|.input-sm|.header-search-current .jump-to-suggestions|.m-0|.flex-justify-start|.header-search-current .jump-to-suggestions-results-container .navigation-item|.p-2|.header-search-current .jump-to-suggestions-path|.text-center|.header-search-current .jump-to-suggestions-path .jump-to-octicon|.no-wrap|.header-search-current .jump-to-suggestions-path .jump-to-suggestion-name|.text-gray-light|.pl-1, .px-1|.px-1|.header-search-current .jump-to-suggestions-results-container .d-on-nav-focus|.ml-1|.m-2|.mr-3|.mt-n3|.mb-n3|.py-3|.notification-indicator|.notification-indicator .mail-status|.dropdown-header|.mr-0|.Header, .Header-item|.Header-item|.Header-link|.avatar|.avatar-user|.header-nav-current-user|.mt-n1|.mb-n2|.pt-2|.header-nav-current-user .user-profile-link|b, strong|.header-nav-current-user .css-truncate-target|.pr-3|.user-status-container, .user-status-container .team-mention, .user-status-container .user-mention|.user-status-container|.border|.py-1|.pl-2, .px-2|.px-2|.btn-block|.circle|.flex-justify-center|.flex-shrink-0|.v-align-bottom|.lh-condensed-ultra|.min-width-0|.user-status-message-wrapper|.css-truncate.css-truncate-overflow, .css-truncate .css-truncate-overflow, .css-truncate.css-truncate-target, .css-truncate .css-truncate-target|.css-truncate.css-truncate-target, .css-truncate .css-truncate-target|.width-fit|.user-status-message-wrapper div|.text-gray|.anim-fade-in|.anim-fade-in.fast|details-dialog|.Box--overlay|.rounded-1|.flex-auto|.Box-header|.bg-gray|.Box--overlay .Box-header|.Box-btn-octicon.btn-octicon|.btn-octicon|h3|.Box-title|.py-2|.form-group|.input-group|.d-table|.input-group-button|.btn-outline|.input-group-button:first-child .btn|.p-0|.user-status-container .input-group-button .btn|.form-group .form-control|.input-group .form-control|.input-group-button, .input-group .form-control|.input-group-button:first-child .btn, .input-group .form-control:first-child|.input-group-button:last-child .btn, .input-group .form-control:last-child|.d-table-cell|.form-group .error, .form-group .indicator, .form-group .success|.my-1|.text-small|.label-characters-remaining|.mr-n3|.ml-n3|.pl-3, .px-3|.px-3|.overflow-hidden|.user-status-suggestions|.user-status-suggestions.collapsed|h3, h4|h4|.my-3|.text-normal|.mx-3|.col-6|.float-left|.link-gray|.flex-items-baseline|.flex-items-stretch|.emoji-status-width|g-emoji|.text-left|.no-underline|.user-status-limited-availability-container|.form-checkbox|.my-0'.
//$query  = '|'.
//$query = '\\[type = "checkbox"\\]'; // , \[type="radio"\]|.form-checkbox input\[type="checkbox"\], .form-checkbox input\[type="radio"\]|label|.text-gray-dark|.d-block|.form-checkbox .note|.note|.pb-2|.pt-3|.f5|.dropdown|.v-align-baseline|.btn .dropdown-caret|.dropdown-caret|.overflow-auto|.pl-0, .px-0|.mb-1|.text-bold|.lh-condensed|.ws-normal|.border-top|.flex-justify-between|.flex-items-center|.p-3|.d-flex|.btn-primary.disabled, .btn-primary:disabled, .btn-primary\[aria-disabled="true"\]|.mr-2|.btn.disabled, .btn:disabled, .btn\[aria-disabled="true"\]|.dropdown-divider|.position-relative|.feature-preview-indicator|.feature-preview-details .feature-preview-indicator|.dropdown-item.btn-link, .dropdown-signout|.dropdown-signout|.border-bottom|.mb-0|.pb-4|.shelf|.intro-shelf|.shelf .container|.mx-auto|.shelf-content|h1, h2, h3, h4, h5, h6|h1, h2|h2|.shelf-title|p|.shelf-lead|.intro-shelf .shelf-lead|.btn-primary|.shelf-cta|.shelf-dismiss|.close-button|.tooltipped|.mr-1|.shelf-dismiss .close-button|.v-align-text-top|.mt-5|.select-menu-list|.select-menu-item.selected, details-menu .select-menu-item\[aria-checked="true"\], details-menu .select-menu-item\[aria-selected="true"\]|.select-menu-item.selected \> .octicon, details-menu .select-menu-item\[aria-checked="true"\] \> .octicon, details-menu .select-menu-item\[aria-selected="true"\] \> .octicon|.select-menu-item.selected .octicon-check, .select-menu-item.selected .octicon-circle-slash, details-menu .select-menu-item\[aria-checked="true"\] .octicon-check, details-menu .select-menu-item\[aria-checked="true"\] .octicon-circle-slash, details-menu .select-menu-item\[aria-selected="true"\] .octicon-check, details-menu .select-menu-item\[aria-selected="true"\] .octicon-circle-slash|.select-menu-item.selected .description, details-menu .select-menu-item\[aria-checked="true"\] .description, details-menu .select-menu-item\[aria-selected="true"\] .description|.v-align-text-bottom|.width-full|.select-menu-item.last-visible, .select-menu-list:last-child .select-menu-item:last-child|.select-menu-item|.select-menu-item .octicon-check, .select-menu-item .octicon-circle-slash, .select-menu-item input\[type="radio"\]:not(:checked) + .octicon-check, .select-menu-item input\[type="radio"\]:not(:checked) + .octicon-circle-slash|.select-menu-item-icon|.select-menu-item-text|.select-menu-item-heading|.select-menu-item-text .description|.select-menu-item .hidden-select-button-text|.select-menu-item .octicon|.starring-container.on .unstarred, .starring-container .starred|.btn-sm|.btn-with-count|.btn-sm .octicon|.social-count|.UnderlineNav .Counter|.Counter|.hx_underlinenav-item .Counter|.Counter:empty|.dropdown-menu|.dropdown-menu-sw|ol, ul|.dropdown-menu \> ul|.dropdown-item|.table-list-triage|.table-list-header-meta|.float-right|img|.v-align-middle|.text-red|.f6|article, aside, details, figcaption, figure, footer, header, main, menu, nav, section|.d-inline-block|.table-list-header-toggle .select-menu|summary|details summary|.details-reset \> summary|.table-list-header .btn-link|.table-list-header-toggle .btn-link|.table-list-header-toggle .select-menu-button|details:not(\[open\]) \> :not(summary)|.Box .section-focus .edit-section, \[data-catalyst\], auto-complete, details-dialog, details-menu, file-attachment, filter-input, image-crop, in-viewport, include-fragment, poll-include-fragment, remote-input, tab-container, text-expander|.select-menu-modal|.select-menu-divider, .select-menu-header|.select-menu-divider, .select-menu-header .select-menu-title|.select-menu-filters|.select-menu-filters, .select-menu-header|.select-menu-text-filter|.select-menu-text-filter:first-child:last-child|.form-control, .form-select|.select-menu-text-filter input|.position-fixed|.right-0|.d-none|.flash-full|.issue-reorder-warning|.container|.btn|.btn-link|.top-0|.left-0|.mt-2|.ml-2|.show-on-focus, .sr-only|.show-on-focus|.repo-health.is-loading .repo-health-results, .sortable-button-item:first-of-type .sortable-button\[data-direction="up"\], .sortable-button-item:last-of-type .sortable-button\[data-direction="down"\]|.btn .octicon|.btn .octicon:only-child|.flash-error|.ajax-error-message|.ajax-error-message \> .octicon-alert|button, input|button, select|\[type="reset"\], \[type="submit"\], button, html \[type="button"\]|button, input, select, textarea|button|.flash-close|.flash-close .octicon|.flash-error .octicon|.flash|.flash-warn|.flash-banner|svg:not(:root)|.octicon|.flash .octicon|.flash-warn .octicon|\[hidden\]\[hidden\]|\[hidden\]|a|\[hidden\], template|.position-absolute|.Popover|*|.Box|.box-shadow-large|.Popover-message|.Popover-message--bottom-left, .Popover-message--top-left';
//$query = '[name = foo_bar]';
//$query = '[name $= foo_bar]';
//$query = '[color(@value, "red")]';
//$query = 'a + strong';
//$query = 'a   strong , a  + strong , a > strong ,
//a ~ strong ,
//a || strong';
//$query = 'a || strong';
//$query = ' || ';

$list = (new QueryParser())->parse($query);
var_dump($list);
echo $list;
die;
//echo $list->render(['compress' => true]);
//
//$css = 'a  + strong {
//
//    background: blue;
//}';
//$css = 'a   strong , a  + strong , a > strong ,
//a ~ strong ,
//a || strong {
//
//} ';

//$css = 'input[ name $= "foo_bar" ], strong {
//
//    background: blue;
//}';

$css = '@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}
p:before {
content: "print";
color: rgb(255 0 0 / 1);
}
@media print {

}
/** this is the story */
/** of the princess leia */
/** who was luke sister */
body {
  background-color: green;
  color: #fff;
  font-family: Arial, Helvetica, sans-serif;
}
strong {

}
p {

}
a {

color: white;
}
span {
color: #343434;
}

h1,h2, a {
  color: #fff;
  font-size: 50px;
  font-family: Arial, Helvetica, sans-serif;
  font-weight: bold;
}';

$css = '
p:before {
color: rgb(255 0 0 / 1);
}';

//var_dump(\TBela\CSS\Value::parse($css));
//die;

$compiler = new Compiler();

$compiler->setContent($css);
$element = $compiler->getData();

//var_dump($element['children'][0]->getSelector());
//var_dump(array_map('trim', $element->query($query)));
//var_dump($element->query($query));


echo implode("\n", (new CssParser($css))->parse()->query($query));
//echo $element;