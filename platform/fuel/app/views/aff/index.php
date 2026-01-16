<?php
echo $this->header;
if (isset($this->inside)) {
    echo $this->navbar;
    echo $this->inside;
}
echo $this->footer;
