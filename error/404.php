<?php
header("HTTP/1.0 404 Not Found");
require("../templates/preheader.php");
$pagetitle = "404 Not Found";
require("../templates/header.php");
?>
<div id="feed">
<header class='heading'>There's nothing here...</header>
We've searched far and wide, but there is simply no file which resides at this particular electronic address. Perhaps a file once lived here, before it was mistakenly deleted by a rougue script, or even a simple human error. Or maybe there is no file, never was a file. Perhaps this entire website never even existed; it's simply a figment of your imagination. But one thing is certain: there is no file now. Only the cold void of the 404 error.
<div class="massive">404</div>
</div>
<?php
require("../templates/footer.php");
?>