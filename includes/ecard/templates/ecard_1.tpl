<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<!-- $Id$ -->

<html>
  <head>
    <title>Gallery E-C@rd</title>

    <style type="text/css">
 	body {
		background-color: #FFFFFF;
	}
	table.ecard {
		border:1px solid #666666
	}
    </style>
  </head>

  <body>
    <br>

    <table <%ecard_width%> class="ecard" height="400" align="center" cellpadding="3" cellspacing="0">
      <tr>
        <td rowspan="2" align="center"><img src="<%ecard_image_name%>" border="1"></td>
        <td height="30%" width="200" align="right" valign="top">
          <img src="<%ecard_stamp%>"><br>
          <br>
           E-C@rd from <b><%ecard_sender_name%></b>
	</td>
      </tr>

      <tr>
        <th align="left" valign="top" scope="col"><%ecard_message%></th>
      </tr>
    </table>
  </body>
</html>

