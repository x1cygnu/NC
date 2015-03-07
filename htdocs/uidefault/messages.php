<?php
if (count($Messages)>0)
  $H->addStyleFile($UI.'/messages.css');
foreach ($Messages as $Message) {
  switch ($Message->type) {
    case MessageInfo: $H[] = Paragraph()->_("$Message->msg")->setClass('msginfo'); break;
    case MessageSuccess: $H[] = Paragraph()->_("$Message->msg")->setClass('msgsucc'); break;
    case MessageWarning: $H[] = Paragraph()->_("$Message->msg")->setClass('msgwarn'); break;
    case MessageError: $H[] = Paragraph()->_("$Message->msg")->setClass('msgerr'); break;
    default:
  }
}
?>
