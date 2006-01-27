/*
 * Panorama.java
 *
 * Copyright (c) 2000 by Rüdiger Appel, All Rights Reserved.
 *
 * Permission to use, copy, modify, and distribute this software
 * and its documentation for NON-COMMERCIAL purposes and without
 * fee is hereby granted provided that this copyright notice
 * appears in all copies. 
 *
 * http://www.3quarks.com
 *
 */


import java.applet.*;
import java.awt.*;
import java.util.*;


// class Panorama

public class Panorama extends Canvas implements Runnable
  {
  private Thread   animation     = null;
  private Image    tile          = null;
  private Image    buffer        = null;
  private Vector   imageList     = null;
  
  private Scroller scroller      = null;
  
  private int      imagePosX;
  private int      panoramaWidth;
  private int      panoramaHeight;
  private int      panoramaPosX;
  private int      panoramaPosY;
  private int      animationOffset;
  private int      delay;
  
  private int      mousePosX;
  private int      mousePosY;
  private int      savePosX;
  private int      savePosY;
  
    
  public Panorama (int width, int height, int canvasHeight, int delay)
    {
    // init panorama size and position
    panoramaWidth  = width;
    panoramaHeight = height;
    panoramaPosX   = 0;
    
    // set panorama y position
    if (canvasHeight == panoramaHeight)
      panoramaPosY = 0;
    else
      panoramaPosY = (canvasHeight - panoramaHeight) / 2;
    
    // init animation offset
    animationOffset = 0;
    
    // init panorama delay
    this.delay = delay;
    
    // create image list
    imageList = new Vector ();
    imagePosX = 0;
    
    // start animation
    if (delay > 0)
      {
      animation = new Thread (this);
      animation.start ();
      }
    }

  
  public void setAnimationOffset (int offset)
    {
    animationOffset = offset;
    }
  
  
  public void setTile (Image tile)
    {
    // set background tile
    this.tile = tile;

    // repaint component
    if (tile != null)
      repaint ();
    }
  
  
  public void setScroller (Scroller scroller)
    {
    // set panorama scroller
    this.scroller = scroller;
    }
  
    
  public void addPanoramaImage (Image image)
    {
    // add panorama image to list
    addPicture (image, null, 0, imagePosX, 0, 1, 1);
    imagePosX += image.getWidth (this);
    }
  
  
  public Picture addPicture (Image image, String name, int index, int x, int y, int count, int delay)
    {
    // get image width and height
    int width  = image.getWidth (this);
    int height = image.getWidth (this);
    
    // correct the x position
    if ((x + width) > panoramaWidth)
      x -= panoramaWidth;
    
    // add picture to list
    Picture picture = new Picture (image, name, index, x, y, width, height, count, delay);
    imageList.addElement (picture);
      
    // repaint panorama if picture is visible
    int x1 = x - panoramaPosX;
    if (x1 < (size ().width - panoramaWidth))
      x1 = panoramaWidth - panoramaPosX + x;
    if (((x1 + width) > 0) && (x1 < size ().width))
      repaint ();
    
    return picture;
    }
  
  
  public void update (Graphics graphics)
    {
    // paint component
    paint (graphics);
    }

  
  public void paint (Graphics graphics)
    {
    // get component size
    int width  = size ().width;
    int height = size ().height;
    
    // create buffer if not exist
    if (buffer == null)
      buffer = createImage (width, height);
    
    // get buffer graphics
    Graphics bufferGraphics = buffer.getGraphics ();

    // paint background
    if (tile != null)
      {
      int tileWidth  = tile.getWidth  (this);
      int tileHeight = tile.getHeight (this);
      
      for (int x = -(panoramaPosX % tileWidth); x < width; x += tileWidth)
        for (int y = Math.min (0, panoramaPosY); y < height; y += tileHeight)
          bufferGraphics.drawImage (tile, x, y, this);
      }
    else
      {
      bufferGraphics.setColor (getParent ().getBackground ());
      bufferGraphics.fillRect (0, 0, width, height);
      }
    
    // paint panorama images
    for (int index = 0; index < imageList.size (); index++)
      {
      Picture picture = (Picture) imageList.elementAt (index);
      Rectangle rect = picture.getRect ();
      int x1 = rect.x - panoramaPosX;
      if (x1 < (width - panoramaWidth))
        x1 = panoramaWidth - panoramaPosX + rect.x;
      if (((x1 + rect.width) > 0) && (x1 < width))
        bufferGraphics.drawImage (picture.getImage (), x1, panoramaPosY + rect.y, this);
      }
    
    // paint buffer 
    graphics.drawImage (buffer, 0, 0, this);
    }
  
  
  public boolean mouseDrag (Event event, int x, int y)
    {
    // compute next panorama x position
    panoramaPosX = (savePosX + panoramaWidth - x + mousePosX) % panoramaWidth;
    
    // compute panorama y position
    if (panoramaHeight > size ().height)
      panoramaPosY = Math.min (0, Math.max (size ().height - panoramaHeight, savePosY + y - mousePosY));
        
    // repaint panorama
    repaint ();
    
    return true;
    }

   
  public boolean mouseDown (Event event, int x, int y)
    {
    // stop animation
    animationOffset = 0;

    // save mouse and panorama position
    mousePosX = x;
    mousePosY = y;
    savePosX  = panoramaPosX;
    savePosY  = panoramaPosY;
    
    // inform scroller
    if (scroller != null)
      scroller.reset ();
    
    return true;
    }

  
  public void stop ()
    {
    // stop animation
    if (animation != null)
      {
      animation.stop ();
      animation = null;
      }
    }
  
  
  public void run ()
    {
    // animation thread
    while (true)
      {
      try 
        {
        boolean repaint = false;
        
        // animate panorama images
        for (int index = 0; index < imageList.size (); index++)
          {
          Picture picture = (Picture) imageList.elementAt (index);
          repaint |= picture.animate ();
          }       
        
        // compute next panorama position
        panoramaPosX = (panoramaPosX + animationOffset + panoramaWidth) % panoramaWidth;
        
        // repaint panorama if position changed or repaint flag is set
        if ((animationOffset != 0) || repaint)
          repaint ();
  
        // wait a moment
        animation.sleep (delay);
        }
      catch (InterruptedException exception) {}
      }
    }
  
  }
