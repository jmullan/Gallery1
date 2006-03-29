/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
/**
 * @version $Revision$ $Date$
 * @package Gallery
 * @author Larry Menard
 */

/*
 * g2_db2_bit_or:
 *
 * A Java User-Defined Function (UDF) to implement BITwise OR functionality for DB2.
 *
 * Developed with much thanks to:
 *
 * Knut Stolze (IBM DB2 Development)
 *
 *   - http://www-128.ibm.com/developerworks/db2/library/techarticle/0309stolze/0309stolze.html
 *   - http://www-128.ibm.com/developerworks/db2/library/techarticle/dm-0404stolze/index.html
 *
 * and
 *
 *    - "A Complete Guide to DB2 Universal Database" by Don Chamberlin
 *       (Morgan Kaufmann Publishers Inc, 1998, ISBN 1-55860-482-0)
 *
 * and of course, the DB2 UDB documentation and samples:
 *
 *    - http://publib.boulder.ibm.com/infocenter/db2help/index.jsp
 */

import java.lang.*;          // for String class
import java.io.*;            // for ...Stream classes
import COM.ibm.db2.app.*;    // UDF and associated classes
import java.util.*;          // for HashMap classes

public class g2_db2_bit_or extends UDF
{
    // Hash of current BITOR-ed values for each itemId
    // (The class is instantiated anew for each occurence of the calling UDF
    //  in a SQL statement.  Thus, we can safely reuse the object from one
    //  call to the next as no other UDF-occurence could interfere.)
    HashMap groupMap = null;


    public void g2_db2_bit_or ( int itemId,
	    String permission,
	    Blob intermResult )
	throws Exception
    {
	// Test for SQL NULLs in the input parameters and
	// the structured value itself
	if ( isNull(1) || isNull(2) )
	{
	    return;
	}

	// Byte array for the updateable copy of the permission value
	byte[] workBuffer = new byte[32];

	// HashMap keys need to be 'onject's, a plain old 'int' won't work
	Integer iItemId = new Integer(itemId);

	switch (getCallType())
	{
	  case SQLUDF_FIRST_CALL:
	      groupMap = new HashMap();
	      // No 'break;'... fall through to NORMAL call

	  case SQLUDF_NORMAL_CALL:

	      // If a current BITOR-ed value exists, copy it into the workBuffer array.
	      if (groupMap.containsKey(iItemId) == true)
	      {
		  workBuffer = (byte[]) groupMap.get(iItemId);
	      }
	      // If no current BITOR-ed value exists, just copy the current column value.
	      else
	      {
		  workBuffer = permission.getBytes();
	      }

	      // Convert the column value string into a byte array
	      byte[] columnValueArray = new byte[32];
	      columnValueArray = permission.getBytes();

	      // Walk the permission byte array, test each char.
	      for (int ctr = 0; ctr <= 31; ctr++)
	      {
		  if (workBuffer[ctr] != 49)
		  {
		      // Check the current column value byte.
		      // If it's 1 (ASCII 49 = numeric 1),
		      if (columnValueArray[ctr] == 49)
		      {
			  // then set the corresponding BITOR-ed bit to 1,
			  workBuffer[ctr] = 49;
		      }
		      else
		      {
			  // else set the corresponding BITOR-ed bit to 0.
			  workBuffer[ctr] = 48;
		      }
		  }
	      }

	      // Save the BITOR-ed intermediate result in the Hash Map;
	      groupMap.put(iItemId, workBuffer);

	      // Set output parameter to the intermediate result
	      // (VARCHAR FOR BIT DATA is mapped to "Blob" class)
	      intermResult = Lob.newBlob();
	      OutputStream intermOut = intermResult.getOutputStream();
	      intermOut.write(workBuffer);
	      set(3, intermResult);

	      break;

	}
    }
}

