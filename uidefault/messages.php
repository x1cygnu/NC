<?php
foreach ($Messages as $Message) {
  switch ($Message->type) {
    case MessageInfo: $H[] = Paragraph()->_("Info: $Message->msg"); break;
    case MessageWarning: $H[] = Paragraph()->_("Warning: $Message->msg"); break;
    case MessageError: $H[] = Paragraph()->_("Error: $Message->msg"); break;
    default:
  }
}
?>
