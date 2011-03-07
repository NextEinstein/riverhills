  <p>Seeing and delighting in the glory of God is the purpose
                    of human existence.  God created us to know Him. The sermons
                    we hear each Sunday give us insight into God's attributes
                    and what our appropriate response to Him should be. This
                    month's sermon titles are listed below.</p>

    <table width="100%" border="0" align="center" cellpadding="5" cellspacing="1">
                    <tr bgcolor="#456770">
                      <td width="65"><div align="center"><strong><font color="#FFFFFF">Date</font></strong></div></td>
                      <td width="261"><div align="center"><strong><font color="#FFFFFF">Topic</font></strong></div></td>
                      <td width="75"><div align="center"><strong><font color="#FFFFFF">Play</font></strong></div></td>
                      <td width="75"><div align="center"><strong><font color="#FFFFFF">Download</font></strong></div></td>
                      <td width="100"><div align="center"><strong><font color="#FFFFFF">Scripture</font></strong></div></td>
                    </tr>

<?php

$username = "riverhi1_ronly";
$password = "ronly";
$hostname = "localhost";
$dbh = mysql_connect($hostname, $username, $password)
	or die ('I cannot connect to the database because: ' . mysql_error());
$selected = mysql_select_db("riverhi1_SermonTexts",$dbh)
	or die("Could not select riverhi1_SermonTexts");

if (empty($_POST['Submit'])) {
    $sql1 = "SELECT dateDelivered, seriesname, title, book, beginChapter, textLink, lessonLink, audioLink, guestSpeaker, guestSpeakerName " .
            "FROM sermonlist " .
            "ORDER BY dateDelivered DESC " .
            "LIMIT 0, 30 ";
} else {
    $sBook = !empty($_POST['book']) ? $_POST['book'] : false;
    $sFromChapter = !empty($_POST['fromChapter']) ? $_POST['fromChapter'] : false;
    $sToChapter = !empty($_POST['toChapter']) ? $_POST['toChapter'] : false;
    $sSeries = !empty($_POST['series']) ? $_POST['series'] : false;

    if ($sSeries == "") {
      $sql1 = "SELECT dateDelivered, seriesname, title, book, beginChapter, textLink, lessonLink, audioLink, guestSpeaker, guestSpeakerName " .
              "FROM sermonlist " .
                      "WHERE book = '" . $sBook . "' AND beginChapter BETWEEN " . $sFromChapter . " AND " . $sToChapter . " " .
              "ORDER BY dateDelivered, book, beginChapter ";
    } else {
      $sql1 = "SELECT dateDelivered, seriesname, title, book, beginChapter, textLink, lessonLink, audioLink, guestSpeaker, guestSpeakerName " .
              "FROM sermonlist " .
                      "WHERE seriesname = '" . $sSeries . "' " .
              "ORDER BY dateDelivered, book, beginChapter";
    }
}
$result = mysql_query($sql1);
$counter = 0;
while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
  if ($counter == 0)   {
    $cellcolor = "#878262";
  }
  else   {
    $cellcolor = "#edecd8";
  }
  if ($row["guestSpeaker"] == 1)  {
    $GuestSpeakerName = "(Guest Speaker : " . $row["guestSpeakerName"] . ")";
    $GuestSpeaker = "&GuestSpeaker=1";
  }
  else  {
    $GuestSpeakerName = "";
    $GuestSpeaker = "&GuestSpeaker=0";
  }
  print "<tr bgcolor=" . $cellcolor . ">";
  print "<td><p>" . $row["dateDelivered"] . "</p></td>";
  print "<td><p>{$row["seriesname"]} - {$row["title"]} {$GuestSpeakerName }
<a href=\"http://riverhillsonline.org/resources/documents/sermons/{$row["textLink"]}\" class=\"readmore\">Read</a><img src=http://riverhillsonline.org/images/arrow.gif width=12 height=12 align=absmiddle>";
  if (trim($row["lessonLink"]) <> "") {
    print "<a href=documents/lessons/" . $row["lessonLink"] . " class=readmore> Lesson</a><img src='http://riverhillsonline.org/images/arrow.gif' width=12 height=12 align='absmiddle'>";
  }
  print "</p></td>";
  print "<td><div align=center>";
  if (trim($row["audioLink"]) <> "") {
    print "<a href=http://riverhillsonline.org/audio.php?SermonDate=" .
      $row["audioLink"] .
      $GuestSpeaker .
      " class=readmore><img src=http://riverhillsonline.org/images/play.gif border=0></a>";
  }
  print "</div></p></td>";
  print "<td><div align=center>";
  if (trim($row["audioLink"]) <> "") {
    print "<a href=http://riverhillsonline.org/download.php?file=audio/" .
      $row["audioLink"] .
      ".mp3><img src=http://riverhillsonline.org/images/download1.gif border=0></a>";
  }
  print "</div></p></td>";
  print "<td><p>" . $row["book"] . "&nbsp;" . $row["beginChapter"] . "</p></td>";
  print "</tr>";
  print "\n";

  $counter += 1;
  IF ($counter == 2)   {
    $counter = 0;
  }
}
mysql_free_result($result);
mysql_close($dbh);
?>
    </table>



