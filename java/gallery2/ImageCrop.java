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

import java.applet.Applet;
import java.awt.Color;
import java.awt.Dimension;
import java.awt.Graphics;
import java.awt.Image;
import java.awt.Point;
import java.awt.Rectangle;
import java.awt.Cursor;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;
import java.awt.event.MouseMotionListener;
import java.awt.image.ImageProducer;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLConnection;
import java.io.IOException;

public class ImageCrop extends Applet {
    private static final int LANDSCAPE = 0;
    private static final int PORTRAIT = 1;
    private int mCropOrientation = LANDSCAPE;
    private Image mOffscreenImage;
    private Image mImage;
    private boolean mImageLoaded = false;
    private Rectangle mCanvasRect = new Rectangle(10, 10, 610, 460);
    private Rectangle mImageRect = new Rectangle(mCanvasRect);
    private Rectangle mCropRect = new Rectangle(mCanvasRect);
    private Rectangle mResizeHandleRect = new Rectangle(0, 0, 10, 10);
    private Dimension mRawImageSize = new Dimension(4, 3);
    private int mCropToSize = 0;
    private boolean mCropTooSmall = false;
    private float mImageScale;
    private String mStatus;
    private Dimension mCropRatio = new Dimension(4, 3);
    private Cursor resizeCursor = new Cursor(Cursor.SE_RESIZE_CURSOR);
    private Cursor normalCursor = new Cursor(Cursor.DEFAULT_CURSOR);
    private Cursor moveCursor = new Cursor(Cursor.MOVE_CURSOR);

    /**
     * Get the crop orientation
     * @return
     */
    public int getCropOrientation() {
	return mCropOrientation;
    }

    /**
     * Set the crop orientation
     * @param cropOrientation
     */
    public void setCropOrientation(int cropOrientation) {
	mCropOrientation = cropOrientation;
    }

    /**
     * Set the crop orientation
     * @param cropOrientation
     */
    public void setCropOrientation(String cropOrientation) {
	if ("landscape".equals(cropOrientation)) {
	    setCropOrientation(LANDSCAPE);
	} else if ("portrait".equals(cropOrientation)) {
	    setCropOrientation(PORTRAIT);
	}
	constrainCrop();
	updateHandles();
	invalidate();
	repaint();
    }

    /**
     * Destroy the applet
     */
    public void destroy() {
	if (mImage != null) {
	    mImage.flush();
	}

	if (mOffscreenImage != null) {
	    mOffscreenImage.flush();
	}
    }

    /**
     * Get the integer value of the given parameter
     * @param name the name of the parameter
     * @return an integer value
     */
    private int getIntParameter(String name) {
	String value = getParameter(name);
	if (value == null) {
	    return 0;
	}

	return Integer.parseInt(value);
    }

    /**
     * Initialize our applet.  Begin loading the image and get everything ready for
     * when the image is available.
     *
     * @see Applet#init
     */
    public void init() {
	setStatus("Initializing applet...");

	//-- the mImage size ---
	mRawImageSize.width = getIntParameter("IMAGE_WIDTH");
	mRawImageSize.height = getIntParameter("IMAGE_HEIGHT");
	if (mRawImageSize.width == 0 || mRawImageSize.height == 0) {
	    setStatus("Invalid Image Size Parameters");
	    return;
	}

	setCropOrientation(getParameter("CROP_ORIENTATION"));

	//-- crop rectangle. if provided (otherwise, defaults assigned later) ---
	mCropRect.x = getIntParameter("CROP_X");
	mCropRect.y = getIntParameter("CROP_Y");
	mCropRect.width = getIntParameter("CROP_WIDTH");
	mCropRect.height = getIntParameter("CROP_HEIGHT");

	mCropRatio.width = getIntParameter("CROP_RATIO_WIDTH");
	mCropRatio.height = getIntParameter("CROP_RATIO_HEIGHT");

	mCropToSize = getIntParameter("CROP_TO_SIZE");

	//-- the mImage (prepend full URL) ---
	String imageUrlString = getParameter("IMAGE");
	if (imageUrlString == null) {
	    setStatus("No Image URL parameter provided.");
	    return;
	}

	//-- set up the UI ---
	setStatus("Loading image...");
	setBackground(Color.white);
	setLayout(null);

	try {
	    URLConnection conn = new URL(getCodeBase(), imageUrlString).openConnection();
	    //-- Set http referer so G2 hotlink protection won't block request
	    conn.setRequestProperty("Referer", getCodeBase().toString());
	    mImage = this.createImage((ImageProducer)conn.getContent());
	} catch (MalformedURLException e) {
	    setStatus("Invalid URL: " + imageUrlString);
	    return;
	} catch (IOException e) {
	    setStatus("Error loading image: " + imageUrlString);
	    return;
	}

	//-- start loading the mImage resource. when it's done, updateImage()
	//-- will notice and finish initialization.
	this.prepareImage(mImage, this);

	MyMouseListener mouseListener = new MyMouseListener();
	addMouseListener(mouseListener);
	addMouseMotionListener(mouseListener);
    }

    /**
     * @see Applet#invalidate
     */
    public void invalidate() {
	super.invalidate();
	mOffscreenImage = null;
    }

    /**
     * @see Applet#update
     * @param g
     */
    public void update(Graphics g) {
	paint(g);
    }

    /**
     * @see Applet#paint
     */
    public void paint(Graphics g) {
	if (mOffscreenImage == null) {
	    mOffscreenImage = createImage(getSize().width, getSize().height);
	}
	Graphics og = mOffscreenImage.getGraphics();
	og.setClip(0, 0, getSize().width, getSize().height);

	//-- the background ---
	og.setColor(Color.black);
	og.drawRect(0, 0, getSize().width - 1, getSize().height - 1);

	if (mImageLoaded) {
	    int x, y, width, height;

	    //-- draw a bgcolor cleanup rect last ---
	    og.setColor(Color.black);
	    og.fillRect(mImageRect.x + 1, mImageRect.y + 1,
			mImageRect.width, mImageRect.height);

	    //-- draw the mImage first ---
	    og.drawImage(mImage,
			 mImageRect.x, mImageRect.y, mImageRect.width, mImageRect.height,
			 Color.white,
			 this);

	    //-- crop box color ---
	    Color cropColor = (mCropTooSmall) ? Color.red : Color.cyan;

	    //-- draw the resize handle ---
	    x = mResizeHandleRect.x + mImageRect.x;
	    y = mResizeHandleRect.y + mImageRect.y;
	    width = mResizeHandleRect.width;
	    height = mResizeHandleRect.height;

	    og.setColor(Color.black);
	    og.drawRect(x + 1, y + 1, width, height);
	    og.setColor(Color.white);
	    og.drawRect(x - 1, y - 1, width, height);
	    og.setColor(cropColor);
	    og.fillRect(x, y, width, height);

	    //-- draw the crop rect ---
	    x = mCropRect.x + mImageRect.x;
	    y = mCropRect.y + mImageRect.y;
	    width = mCropRect.width;
	    height = mCropRect.height;

	    og.setColor(Color.black);
	    og.drawRect(x + 1, y + 1, width, height);
	    og.setColor(Color.white);
	    og.drawRect(x - 1, y - 1, width, height);
	    og.setColor(cropColor);
	    og.drawRect(x, y, width, height);
	} else {
	    /* Render our status message */
	    String status = getStatus();
	    if (status != null) {
		og.drawString(getStatus(), 100, 100);
	    }
	}

	super.paint(og);
	g.drawImage(mOffscreenImage, 0, 0, null);
	og.dispose();
    }

    /**
     * Once the mImage is loaded, set up the coord space and all.
     */
    private void finishInitWithImage() {
	mImageLoaded = true;

	//-- figure out how to scale the mImage within the canvas ---
	Dimension size = new Dimension(mImage.getWidth(this), mImage.getHeight(this));
	if (size.width > size.height) {
	    mImageRect.width = (size.width > mCanvasRect.width) ?
		mCanvasRect.width : size.width;
	    float scale = (float)mImageRect.width / (float)size.width;
	    mImageRect.height = (int)(scale * size.height);
	} else {
	    mImageRect.height = (size.height > mCanvasRect.height) ?
		mCanvasRect.height : size.height;
	    float scale = (float)mImageRect.height / (float)size.height;
	    mImageRect.width = (int)(scale * size.width);
	}
	mImageRect.x = mCanvasRect.x + ((mCanvasRect.width - mImageRect.width) / 2);
	mImageRect.y = mCanvasRect.y + ((mCanvasRect.height - mImageRect.height) / 2);

	//-- how big is the onscreen mImage compared to the raw mImage ---
	mImageScale = (float)mRawImageSize.width / (float)mImageRect.width;

	if ((mCropRect.width > 0) && (mCropRect.height > 0)) {
	    //-- we need to scale it down to match scaled down mImage ---
	    mCropRect.x = (int)((float)mCropRect.x / mImageScale);
	    mCropRect.y = (int)((float)mCropRect.y / mImageScale);
	    mCropRect.width = (int)((float)mCropRect.width / mImageScale);
	    mCropRect.height = (int)((float)mCropRect.height / mImageScale);
	} else {
	    //-- assign 'As Image' as default aspect ratio ---
	    //-- the default size is the biggest possible ---
	    mCropRect.x = 0;
	    mCropRect.y = 0;
	    mCropRect.width = mImageRect.width;
	    mCropRect.height = mImageRect.height;
	}
	mCropToSize = (int)((float)(mCropToSize)/mImageScale);

	//-- guess the aspect ration, based on the crop dimensions ---
	if (mCropRect.width < mCropRect.height) {
	    mCropOrientation = PORTRAIT;
	} else {
	    mCropOrientation = LANDSCAPE;
	}

	//-- then squeeze the crop to make sure it fits ---
	constrainCrop();
	updateHandles();
    }


    /**
     * Constrain the crop rectangle to the required aspect ratio.  Also track if we've made the crop
     * box smaller than the crop-to size.
     */
    private void constrainCrop() {
	//-- is the crop smaller than the crop-to size ? ---
	if (mCropToSize > 0) {
	    if ((mCropOrientation == LANDSCAPE) && (mCropRect.width < mCropToSize)) {
		mCropTooSmall = true;
	    } else if ((mCropOrientation == PORTRAIT) && (mCropRect.height < mCropToSize)) {
		mCropTooSmall = true;
	    } else {
		mCropTooSmall = false;
	    }
	}

	//-- fix aspect ratio ---
	float cropRatioSlope;
	switch(mCropOrientation) {
	case LANDSCAPE:
	    cropRatioSlope = (float)getCropRatio().height/(float)getCropRatio().width;
	    break;

	default:
	case PORTRAIT:
	    cropRatioSlope = (float)getCropRatio().width/(float)getCropRatio().height;
	    break;
	}

	float cropRectSlope = (float)mCropRect.height/(float)mCropRect.width;
	if (cropRectSlope > cropRatioSlope) {
	    int adjustedHeight = (int)Math.ceil((float)(mCropRect.width)*cropRatioSlope);
	    mCropRect.height = adjustedHeight;
	} else {
	    int adjustedWidth = (int)Math.ceil((float)(mCropRect.height)/cropRatioSlope);
	    mCropRect.width = adjustedWidth;
	}
    }

    public int getCropX() {
	return  (int)((float)mCropRect.x * mImageScale);
    }

    public int getCropY() {
	return  (int)((float)mCropRect.y * mImageScale);
    }

    public int getCropWidth() {
	return (int)Math.ceil((float)mCropRect.width * mImageScale);
    }

    public int getCropHeight() {
	return (int)Math.ceil((float)mCropRect.height * mImageScale);
    }

    /**
     * @see Applet#imageUpdate
     */
    public boolean imageUpdate(Image image, int infoFlags,
			       int x, int y, int w, int h) {

	if ((infoFlags & ERROR) > 0) {
	    setStatus("Error loading image");
	} else if (infoFlags == ALLBITS) {
	    if (!mImageLoaded) {
		finishInitWithImage();
	    }
	    invalidate();
	    repaint();
	}

	return super.imageUpdate(image, infoFlags, x, y, w, h);
    }

    /**
     * Set the status string
     * @return
     */
    public String getStatus() {
	return mStatus;
    }

    /**
     * Get the status string
     * @param status
     */
    public void setStatus(String status) {
	mStatus = status;
    }

    public Dimension getCropRatio() {
	return mCropRatio;
    }

    public void setCropRatio(int width, int height) {
	mCropRatio.width = width;
	mCropRatio.height = height;
	constrainCrop();
	updateHandles();
	invalidate();
	repaint();
    }

    /**
     * Updated the location of our resize handle
     */
    public void updateHandles() {
	mResizeHandleRect.x = mCropRect.x + mCropRect.width - mResizeHandleRect.width;
	mResizeHandleRect.y = mCropRect.y + mCropRect.height - mResizeHandleRect.height;
    }

    class MyMouseListener extends MouseAdapter implements MouseMotionListener {
	private boolean mOnMoveHandle = false;
	private boolean mOnResizeHandle = false;
	private Point mMouseDownPoint;

	public void mouseClicked(MouseEvent e) {
	    if (e.getClickCount() == 2) {
		mCropRect.x = 0;
		mCropRect.y = 0;
		mCropRect.width = mImageRect.width;
		mCropRect.height = mImageRect.height;
		constrainCrop();
		updateHandles();
		invalidate();
		repaint();
	    }
	}

	public void mousePressed(MouseEvent e) {
	    mMouseDownPoint = e.getPoint();
	    mMouseDownPoint.x -= mImageRect.x;
	    mMouseDownPoint.y -= mImageRect.y;

	    //--  ---
	    if (mResizeHandleRect.contains(mMouseDownPoint)) {
		mOnResizeHandle = true;
	    } else if (mCropRect.contains(mMouseDownPoint)) {
		//-- currently, anywhere in the box means move ---
		mOnMoveHandle = true;
	    }
	}

	public void mouseReleased(MouseEvent e) {
	    mOnMoveHandle = false;
	    mOnResizeHandle = false;
	}

	public void mouseDragged(MouseEvent e) {
	    Point mouseNow = e.getPoint();
	    mouseNow.x -= mImageRect.x;
	    mouseNow.y -= mImageRect.y;
	    int mouseDiff_x = mouseNow.x - mMouseDownPoint.x;
	    int mouseDiff_y = mouseNow.y - mMouseDownPoint.y;
	    boolean redraw = false;

	    //-- are we moving it? ---
	    if (mOnMoveHandle) {

		if ((mCropRect.x + mCropRect.width + mouseDiff_x < mImageRect.width) &&
		    (mCropRect.x + mouseDiff_x > 0)) {
		    mCropRect.x += mouseDiff_x;
		    redraw = true;
		}
		if ((mCropRect.y + mCropRect.height + mouseDiff_y < mImageRect.height) &&
		    (mCropRect.y + mouseDiff_y > 0)) {
		    mCropRect.y += mouseDiff_y;
		    redraw = true;
		}
	    }

	    //-- are we resizing ---
	    if (mOnResizeHandle) {
		if ((mCropRect.x + mCropRect.width + mouseDiff_x <= mImageRect.width) &&
		    (mCropRect.width + mouseDiff_x >= 10)) {
		    mCropRect.width += mouseDiff_x;
		    redraw = true;
		}
		if ((mCropRect.y + mCropRect.height + mouseDiff_y <= mImageRect.height) &&
		    (mCropRect.height + mouseDiff_y >= 10)) {
		    mCropRect.height += mouseDiff_y;
		    redraw = true;
		}
		constrainCrop();
	    }
	    mMouseDownPoint = mouseNow;

	    if (redraw) {
		//-- need to shift mMouseDownPoint ---
		updateHandles();
		invalidate();
		repaint();
	    }
	}

	public void mouseMoved(MouseEvent e) {

		Point mouseNow = e.getPoint();
		mouseNow.x -= mImageRect.x;
		mouseNow.y -= mImageRect.y;

		if (mResizeHandleRect.contains(mouseNow)) {
		    setCursor(resizeCursor);
		} else if (mCropRect.contains(mouseNow)) {
		    setCursor(moveCursor);
		} else {
		    setCursor(normalCursor);
		}
	}
    }
}
