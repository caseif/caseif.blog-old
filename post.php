<?php
require($_SERVER['DOCUMENT_ROOT'].'/templates/preheader.php');
$id = $_GET['id'];
if (!isset($id))
	header('Location: /');
$link = getDbConnection();
$query = $link->query('SELECT * FROM posts WHERE id = "'.$id.'"');
$numrows = $query->num_rows;
if ($numrows == 1){
	while ($post = $query->fetch_array()){
		if ($post['visible'] == 0){
			header('Location: /error/403.php');
			return;
		}
		$id = $post['id'];
		$title = $post['title'];
		$content = $post['content'];
        $parser = new Parsedown();
		$content = $parser->text(syntaxPreprocessor($content));
		$authorid = $post['author'];
		$category = $post['category'];
		$bitly = $post['bitly'];
		$timestamp = $post['time'];
		$time = getdate($timestamp);
		$year = $time['year'];
		$month = $time['month'];
		$day = $time['mday'];
        $author = $link->query("SELECT * FROM login WHERE id='".$authorid."'")->fetch_array()['display'];
	}
}
$link->close();
$pagetitle = $title.' &mdash; caseif.net';
require($_SERVER['DOCUMENT_ROOT'].'/templates/header.php');
?>
<div id="feed">
<?php
	if ($numrows == 1) {
		if ($_POST['submitcomment']) {
			if ($userid && $username) {
                $link = getDbConnection();
				$userbans = $link->query('SELECT * FROM bans WHERE type = 0 AND value = "'.$userid.'" AND expire <= 0 OR expire < '.time())->num_rows;
				$ipbans = $link->query('SELECT * FROM bans WHERE type = 1 AND value = "'.$_SERVER['REMOTE_ADDR'].'" AND expire = -1 OR expire < '.time())->num_rows;
				if ($ipbans == 0 && $userbans == 0) {
					if (strlen($_POST['newcomment']) >= 10){
						$link->query('INSERT INTO comments (content, post, time, ip, user) VALUES ("'.$_POST['newcomment'].'", "'.$id.'", "'.time().'", "'.$_SERVER['REMOTE_ADDR'].'", "'.$userid.'")') or die ('Failed to post comment!');
					} else {
						$commenterror = 'Your comment must be at least 10 characters!';
                    }
				} else {
					$commenterror = 'An error occurred while submitting your comment. Please try again later.';
                }
				$link->close();
			} else {
				$commenterror = 'You must be <a href="login.php">signed in</a> to post a comment!';
            }
		}
		echo '<article><header class="heading">'.$title.'</header>';
		echo $content;
		echo '<footer>';
		echo '<div class="sig"><span style="float:right">Written by '.$author.' on '.$month.' '.$day.', '.$year.'</span></div>';
		echo '<div class="fb-like" data-href="http://www.caseif.net/post.php?id=$id" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false" data-font="arial"></div>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<g:plusone></g:plusone>';
		$link = getDbConnection();
		echo '<br>';
		if ($userid == 1) {
			echo '<div class="postcontrol"><a href="edit.php?id='.$id.'">Edit</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="delete.php?id='.$id.'">Delete</a></div>';
        }
		echo '</footer></article>';
		echo '<div id="comments">';
		if (isset($commenterror)) {
			echo '<span style="color:red;text-decoration:bold;">'.$commenterror.'</span>';
        }
		if ($userid && $username) {
			echo '<form style="padding-bottom:15px;" action="post.php?id='.$id.'" method="post">
				<b>Post a Comment</b>
				<br>
				<textarea rows=10 cols=80 name="newcomment"></textarea>
				<br>
				<input type="submit" name="submitcomment" value="Submit">
			</form>';
		}
		else {
			echo '<a href="/login.php">Log in</a> or <a href="/register.php">register</a> to post a comment!<br><br>';
		}
		$query = $link->query('SELECT * FROM comments WHERE post = "'.$id.'" ORDER BY time DESC') or die('Failed to fetch comments!');
		if ($query->num_rows > 0) {
			while ($post = $query->fetch_array()) {
				$content = $post['content'];
				$time = $post['time'];
				$ip = $post['id'];
				$user = $link->query('SELECT display FROM login WHERE id = '.$post['user'])->fetch_array()['display'];
				echo '<div class="comment" id="comment_'.$post['id'].'">';
				echo '<div class="commentheader"><span class="commentauthor">'.$user.'</span> on <span class="commenttime">'.date('m/d/Y g:i a ', $time).'</span></div>';
				echo '<div class="commentbody">'.$content.'</div>';
				if ($post['user'] == $userid || $userid == 1) {
					echo '<div class="commentcontrol"><a href="javascript:confirmDeleteComment('.$post['id'].');">Delete</a></div>';
                }
				echo '</div>';
			}
		} else {
			echo 'No comments to display!';
        }
		echo '</div>';
		$link->close();
	} else {
        header('Location: /error/404.php');
    }
?>
</div>
<?php
require($_SERVER['DOCUMENT_ROOT'].'/templates/footer.php');
?>