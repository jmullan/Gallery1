/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

import java.io.*;
import java.applet.*;
import java.awt.*;
import java.awt.event.*;
import java.awt.image.ImageObserver;
import java.util.StringTokenizer;
import java.util.Vector;
import java.util.Enumeration;
import java.net.URL;

public class ImageCrop extends Applet
{
	private static final int LANDSCAPE = 0;
	private static final int PORTRAIT = 1;

	Image offscreen;
	Image image;
	ImageObserver imageObserver;
	Color bgcolor = Color.white;
	boolean imageLoaded = false;
	boolean errorOccurred;
	String errorMessage = "";

	//-- 
	Rectangle canvasRect = new Rectangle(10, 10, 440, 375);
	Rectangle widgetRect = new Rectangle(10, 395, 440, 30);
	Rectangle imageRect = new Rectangle(canvasRect); 
	Rectangle cropRect = new Rectangle(canvasRect); 
	Rectangle resizeHandleRect = new Rectangle(0, 0, 10, 10); 
	Dimension rawImageSize = new Dimension(4, 3);
	int cropToSize = 0;
	int cropLayout = LANDSCAPE;
	boolean cropTooSmall = false;
	AspectRatio cropRatio;
	Vector aspectRatios;
	float imageScale;

	boolean mouseDown_onMoveHandle = false;
	boolean mouseDown_onResizeHandle = false;
	Point mouseDownPoint;

	//-- UI elements ---
	Choice choice_Ratio;
	Choice choice_Layout;


	//----------------------------------------------------------------------
	//----------------------------------------------------------------------
	public ImageCrop()
	{
	}

	//----------------------------------------------------------------------
	//----------------------------------------------------------------------
	public void destroy()
	{
		if (image != null) image.flush();
		if (offscreen != null) offscreen.flush();
	}
	//----------------------------------------------------------------------
	// getStringParameter - gets the applet parameter. If it is missing
	//						or invalid, we set it to zero.
	//----------------------------------------------------------------------
	public int getIntParameter(String name)
	{
		String value = getParameter(name);
		if (value == null) return 0;

		try {
			int intValue = Integer.parseInt(value);
			return intValue;
		} catch (NumberFormatException e) {
			return 0;
		}
	}

	//----------------------------------------------------------------------
	//----------------------------------------------------------------------
	public void init()
	{
		try {

		//-- the image size ---
		rawImageSize.width = getIntParameter("IMAGE_W");
		rawImageSize.height = getIntParameter("IMAGE_H");
		if (rawImageSize.width == 0 || rawImageSize.height == 0)
			throw new Exception("Invalid Image Size Parameters");

		//-- cropLayout starts out like raw image layout ---
		if (rawImageSize.width > rawImageSize.height) cropLayout = LANDSCAPE;
		else cropLayout = PORTRAIT;
 
		//-- crop rectangle. if provided (otherwise, defaults assigned later) ---
		cropRect.x = getIntParameter("CROP_X");
		cropRect.y = getIntParameter("CROP_Y");
		cropRect.width = getIntParameter("CROP_W");
		cropRect.height = getIntParameter("CROP_H");

		cropToSize = getIntParameter("CROP_TO_SIZE");

		//-- the image (prepend full URL) --- 
		String imageUrlString = getParameter("IMAGE");
		if (imageUrlString == null)
			throw new Exception("No Image URL parameter provided.");
		System.out.println("ImageUrl [" + imageUrlString + "]");

		try {
			image = getImage(new URL(getCodeBase(), imageUrlString));
		} catch (java.net.MalformedURLException e) {
			throw new Exception("Malformed Image URL ["+imageUrlString+"]");
		}

		//-- start loading the image resource. when it's done, updateImage() 
		//-- will notice and finish initialization.
		this.prepareImage(image, this);
		
		//-- the cropAspectRatio choices --
		aspectRatios = new Vector(); 
		aspectRatios.addElement(new AspectRatio("As Image", rawImageSize.width, rawImageSize.height));
		aspectRatios.addElement(new AspectRatio("Letterbox", 1, 3));
		aspectRatios.addElement(new AspectRatio("HDTV", 9, 16));
		aspectRatios.addElement(new AspectRatio("Photo", 3, 5));
		aspectRatios.addElement(new AspectRatio("Photo", 4, 6));
		aspectRatios.addElement(new AspectRatio("Photo", 5, 7));
		aspectRatios.addElement(new AspectRatio("Screen", 3, 4));
		aspectRatios.addElement(new AspectRatio("Photo", 8, 10));
		aspectRatios.addElement(new AspectRatio("Square", 1, 1));
		//aspectRatios.addElement(new AspectRatio(1, 1, "Custom.."));

		//-- finally set up the UI ---
		setBackground(Color.white);
		setLayout(null);
		Panel outerBox = new Panel(new BorderLayout());
		Panel widgetBox = new Panel(new FlowLayout(FlowLayout.LEFT, 1, 2));
		outerBox.setSize(widgetRect.width, widgetRect.height);
		outerBox.setLocation(widgetRect.x, widgetRect.y);
		widgetBox.setBackground(Color.white);

		choice_Ratio = new Choice();
		choice_Layout = new Choice();
		choice_Layout.add("Landscape");
		choice_Layout.add("Portrait");
		
		Panel buttonBox = new Panel(new FlowLayout(FlowLayout.RIGHT, 3, 2));
		Button button_Cancel = new Button("Cancel");
		button_Cancel.setSize(100, 25);
		Button button_OK = new Button("OK");
		button_OK.setSize(100, 25);
	   
		widgetBox.add(new Label("Aspect Ratio:"));
		widgetBox.add(choice_Ratio);
		widgetBox.add(choice_Layout);
		buttonBox.add(button_Cancel);
		buttonBox.add(button_OK);
		outerBox.add(widgetBox, BorderLayout.WEST);
		outerBox.add(buttonBox, BorderLayout.EAST);
		add(outerBox);

		updateWidgets(true);
		
		//-- add the listeners last ---
		WidgetItemListener itemListener = new WidgetItemListener();
		choice_Ratio.addItemListener(itemListener);
		choice_Layout.addItemListener(itemListener);

		MyMouseListener mouseListener = new MyMouseListener();
		addMouseListener(mouseListener);
		addMouseMotionListener(mouseListener);

		MyButtonListener buttonListener = new MyButtonListener();
		button_Cancel.addActionListener(buttonListener);
		button_OK.addActionListener(buttonListener);

		//-- catchall for anything bad in init ---
		} catch (Exception e) {
			errorOccurred = true;
			errorMessage = e.toString();
		}

		
	}

	//----------------------------------------------------------------------
	//----------------------------------------------------------------------
	public void invalidate() 
	{
		super.invalidate();
		offscreen = null;
	}

	//----------------------------------------------------------------------
	//----------------------------------------------------------------------
	public void update(Graphics g)
	{
		paint(g);
	}
	//----------------------------------------------------------------------
	//----------------------------------------------------------------------
	public void paint(Graphics g)
	{
		if(offscreen == null) {
		   offscreen = createImage(getSize().width, getSize().height);
		}
		Graphics og = offscreen.getGraphics();
		og.setClip(0,0,getSize().width, getSize().height);

		//-- the background ---
		og.setColor(Color.black);
		og.drawRect(0, 0, getSize().width - 1, getSize().height - 1);

		if (imageLoaded)
		{
			int x, y, width, height;

			//-- draw a bgcolor cleanup rect last ---
			og.setColor(Color.black);
			og.fillRect(imageRect.x + 1, imageRect.y + 1,
						imageRect.width, imageRect.height);

			//-- draw the image first ---
			og.drawImage(image, imageRect.x, imageRect.y, imageRect.width, 
						imageRect.height, bgcolor, this); 

			//-- crop box color ---
			Color cropColor = (cropTooSmall) ? Color.red : Color.cyan;

			//-- draw the resize handle ---
			x = resizeHandleRect.x + imageRect.x;
			y = resizeHandleRect.y + imageRect.y;
			width = resizeHandleRect.width;
			height = resizeHandleRect.height;

			og.setColor(Color.black);
			og.drawRect(x + 1, y + 1, width, height);
			og.setColor(Color.white);
			og.drawRect(x - 1, y - 1, width, height);
			og.setColor(cropColor);
			og.fillRect(x, y, width, height);

			//-- draw the crop rect ---
			x = cropRect.x + imageRect.x;
			y = cropRect.y + imageRect.y;
			width = cropRect.width;
			height = cropRect.height;

			og.setColor(Color.black);
			og.drawRect(x + 1, y + 1, width, height);
			og.setColor(Color.white);
			og.drawRect(x - 1, y - 1, width, height);
			og.setColor(cropColor);
			og.drawRect(x, y, width, height);
		} 
		
		//-- we either got an image or there was an error ---
		else
		{
			if (errorOccurred)
			{
				og.drawString("Error Occurred: " + errorMessage, 100, 100);
			}
			else // assume the image is still loading
			{
				og.drawString("Loading image...", 100, 100);
			}
		}
  
		super.paint(og);
		g.drawImage(offscreen, 0, 0, null);
		og.dispose();
	}

	//----------------------------------------------------------------------
	// finishInitWithImage - once the image is loaded, set up the coord 
	//					   space and all.
	//----------------------------------------------------------------------
	private void finishInitWithImage()
	{
		imageLoaded = true;

		//-- figure out how to scale the image within the canvas ---
		Dimension size = new Dimension(image.getWidth(this), image.getHeight(this));
		if (size.width > size.height)
		{
			imageRect.width = (size.width > canvasRect.width) ? 
								  canvasRect.width : size.width; 
			float scale = (float)imageRect.width / (float)size.width;
			imageRect.height = (int)(scale * size.height);
		}		
		else
		{
			imageRect.height = (size.height > canvasRect.height) ? 
								  canvasRect.height : size.height; 
			float scale = (float)imageRect.height / (float)size.height;
			imageRect.width = (int)(scale * size.width);
		} 
		imageRect.x = canvasRect.x + ((canvasRect.width - imageRect.width) / 2);
		imageRect.y = canvasRect.y + ((canvasRect.height - imageRect.height) / 2);

		//-- how big is the onscreen image compared to the raw image ---
		imageScale = (float)rawImageSize.width / (float)imageRect.width;


		if ((cropRect.width > 0) && (cropRect.height > 0))
		{
			//-- we need to scale it down to match scaled down image ---
			cropRect.x = (int)((float)cropRect.x / imageScale);
			cropRect.y = (int)((float)cropRect.y / imageScale);
			cropRect.width = (int)((float)cropRect.width / imageScale);
			cropRect.height = (int)((float)cropRect.height / imageScale);
		}
		else
		{
			//-- assign 'As Image' as default aspect ratio ---
			//-- the default size is the biggest possible --- 
			cropRect.x = 0;
			cropRect.y = 0;
			cropRect.width = imageRect.width;
			cropRect.height = imageRect.height;
		}
		cropToSize = (int)((float)(cropToSize)/imageScale);
		   
		//-- guess the aspect ration, based on the crop dimensions ---
		if (cropRect.width < cropRect.height) cropLayout = PORTRAIT;
		else cropLayout = LANDSCAPE;

		boolean found = false;
		Enumeration e = aspectRatios.elements();
		while (e.hasMoreElements() && !found)
		{
			AspectRatio r = (AspectRatio)e.nextElement();
			if (r.isMatch(cropRect.width, cropRect.height)) 
			{
				cropRatio = r;
				found = true;
			}
		}
		if (!found) cropRatio = (AspectRatio)aspectRatios.elementAt(0);
		
		//-- then squeeze the crop to make sure it fits ---
		constrainCrop();

		updateHandles();
	}

	//----------------------------------------------------------------------
	// updateHandles 
	//----------------------------------------------------------------------
	public void updateHandles() 
	{
		resizeHandleRect.x = cropRect.x + cropRect.width - resizeHandleRect.width;
		resizeHandleRect.y = cropRect.y + cropRect.height - resizeHandleRect.height;
	}

	//----------------------------------------------------------------------
	// updateWidgets 
	//----------------------------------------------------------------------
	public void updateWidgets(boolean settingControls) 
	{
		//-- if not setting, read the widgets ---
		if (!settingControls)
		{
			cropLayout = choice_Layout.getSelectedIndex();
			cropRatio = (AspectRatio)aspectRatios.elementAt(choice_Ratio.getSelectedIndex());
		}

		//-- rebuild these choices since the names might have changed ---
		boolean selectedOne = false;
		choice_Ratio.removeAll();
		for (Enumeration e = aspectRatios.elements(); e.hasMoreElements(); )
		{
			AspectRatio ratio = (AspectRatio)e.nextElement();
			choice_Ratio.add(ratio.getLabel());
			if (ratio == cropRatio) 
			{
				choice_Ratio.select(ratio.getLabel());
			}
		}
		
		choice_Layout.select(cropLayout);
	}

	//----------------------------------------------------------------------
	class WidgetItemListener implements ItemListener
	{
		public void itemStateChanged(ItemEvent e) 
		{
			updateWidgets(false);
			constrainCrop();
			updateHandles();
			repaint();	
		}
	}
	
	//----------------------------------------------------------------------
	class MyButtonListener implements ActionListener
	{
		public void actionPerformed(ActionEvent e) 
		{
			String submitTo = getParameter("submit") + "&";

			if (((Button)e.getSource()).getLabel() == "OK")
			{
				submitTo += "action=doit";
			}
			else // cancel
			{
				submitTo += "action=cancel";
			}

			//-- append crop params, etc. (need back to scale first) ---
			submitTo += "&crop_x=" + (int)((float)cropRect.x * imageScale);
			submitTo += "&crop_y=" + (int)((float)cropRect.y * imageScale);
			submitTo += "&crop_w=" + (int)((float)cropRect.width * imageScale);
			submitTo += "&crop_h=" + (int)((float)cropRect.height * imageScale);

			System.out.println("Submit [" + submitTo + "]");
			//-- do it ---
			try {
				getAppletContext().showDocument(new URL(submitTo));			
			} catch (Exception e2) {
				System.out.println("Bad submit url [" + submitTo + "]");
			}
		}
	}
   
	//----------------------------------------------------------------------
	//----------------------------------------------------------------------
	class MyMouseListener extends MouseAdapter implements MouseMotionListener
	{

	        public void mouseClicked(MouseEvent e)
	        {
		    if (e.getClickCount() == 2) {
			    cropRect.x = 0;
			    cropRect.y = 0;
			    cropRect.width = imageRect.width;
			    cropRect.height = imageRect.height;
			    constrainCrop();
			    updateHandles();
			    invalidate();
			    repaint();
		    }
		}

		public void mousePressed(MouseEvent e)
		{
			mouseDownPoint = e.getPoint();
			mouseDownPoint.x -= imageRect.x;
			mouseDownPoint.y -= imageRect.y;

			//--  ---
			if (resizeHandleRect.contains(mouseDownPoint))
			{
				mouseDown_onResizeHandle = true;
			}
			//-- currently, anywhere in the box means move ---
			else if (cropRect.contains(mouseDownPoint))
			{
				mouseDown_onMoveHandle = true;
			}
		}
		public void mouseReleased(MouseEvent e)
		{
			mouseDown_onMoveHandle = false;
			mouseDown_onResizeHandle = false;
		}
		public void mouseDragged(MouseEvent e)
		{
			Point mouseNow = e.getPoint();
			mouseNow.x -= imageRect.x;
			mouseNow.y -= imageRect.y;
			int mouseDiff_x = mouseNow.x - mouseDownPoint.x;
			int mouseDiff_y = mouseNow.y - mouseDownPoint.y;
			boolean redraw = false; 

			//-- are we moving it? ---
			if (mouseDown_onMoveHandle)
			{

				if ((cropRect.x + cropRect.width + mouseDiff_x < imageRect.width) &&
					(cropRect.x + mouseDiff_x > 0))
				{
					cropRect.x += mouseDiff_x;
					redraw = true;
				} 
				if ((cropRect.y + cropRect.height + mouseDiff_y < imageRect.height) &&
					(cropRect.y + mouseDiff_y > 0))
				{
					cropRect.y += mouseDiff_y;
					redraw = true;
				} 
			}

			//-- are we resizing ---
			if (mouseDown_onResizeHandle)
			{
				if ((cropRect.x + cropRect.width + mouseDiff_x <= imageRect.width) &&
					(cropRect.width + mouseDiff_x >= 10))
				{
					cropRect.width += mouseDiff_x;
					redraw = true;
				}
				if ((cropRect.y + cropRect.height + mouseDiff_y <= imageRect.height) &&
					(cropRect.height + mouseDiff_y >= 10))
				{
					cropRect.height += mouseDiff_y;
					redraw = true;
				}
				constrainCrop();
			}
			mouseDownPoint = mouseNow;
			
			if (redraw)
			{
				//-- need to shift mouseDownPoint ---
				updateHandles();
				invalidate();
				repaint();
			}
		}
		public void mouseMoved(MouseEvent e)
		{
		}
	}

	//-----------------------------------------------------------------------------
	//-----------------------------------------------------------------------------
	private void constrainCrop()
	{
		//-- is the crop smaller than the crop-to size ? ---
		if (cropToSize > 0)
		{
			if ((cropLayout == LANDSCAPE) && (cropRect.width < cropToSize))
			{
				cropTooSmall = true;
			}
			else if ((cropLayout == PORTRAIT) && (cropRect.height < cropToSize))
			{
				cropTooSmall = true;
			}
			else cropTooSmall = false;
		} 

		//-- fix aspect ratio ---
		float cropRatioSlope = (float)cropRatio.getY()/(float)cropRatio.getX();
		float cropRectSlope = (float)cropRect.height/(float)cropRect.width;
		if (cropRectSlope > cropRatioSlope)
		{
			int adjustedHeight = (int)((float)(cropRect.width)*cropRatioSlope);
			cropRect.height = adjustedHeight;
		}
		else
		{
			int adjustedWidth = (int)((float)(cropRect.height)/cropRatioSlope);
			cropRect.width = adjustedWidth;
		}
	}

	//----------------------------------------------------------------------
	// imageUpdate - override this to make sure the image is loaded
	//			   before we get down to business.
	//----------------------------------------------------------------------
	public boolean imageUpdate(Image img, int infoflags, int x, int y, 
							   int w, int h) 
	{
		//-- as soon as the image is loaded, do the do ---
		if (infoflags == ALLBITS) 
		{ 
			if (!imageLoaded) 
			{
				finishInitWithImage(); 
				updateWidgets(true);
			}
			repaint();
		}
		return super.imageUpdate(img, infoflags, x, y, w, h);
	}
	
	//----------------------------------------------------------------------
	// CLASS AspectRatio - 
	//			Note: this class uses the global outer class 'cropLayout'.
	//----------------------------------------------------------------------
	private class AspectRatio
	{
		public int longSide; //-- the long dimension ---
		public int shortSide; //-- the short dimension ---
		private String label;

		public AspectRatio(String label, int a, int b)
		{
			this(a, b);
			this.label = label;
		}
		 
		public AspectRatio(int a, int b)
		{
			this.longSide = (a < b) ? b : a;
			this.shortSide = (a < b) ? a : b;
		}
		 
		public String getLabel()
		{
			return shortSide + ":" + longSide + " (" + label + ")";
		}

		public int getX()
		{
			return (cropLayout == LANDSCAPE) ? longSide : shortSide;
		}
		public int getY()
		{
			return (cropLayout == LANDSCAPE) ? shortSide : longSide;
		}

		public boolean isMatch(int x, int y)
		{
			float slop = 0.05f;
			if (Math.abs(((float)x/(float)y) - ((float)(this.getX())/(float)(this.getY()))) <= slop)
			{
				return true;
			}
			return false;
		}
	}
} 


















