/*
 * Scroller.java
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


// class Scroller

public class Scroller extends Canvas
  {
  private Image     background  = null;
  private Image     buffer      = null;
  private Image     thumb       = null;
  private Panorama  panorama    = null;
  private Dimension thumbSize   = null;
  private int       thumbPos    = 0;
  private int       thumbMinPos = 0;
  private int       thumbMaxPos = 0;
  
    
  public Scroller (Panorama panorama, Image thumb)
    {
    // set panorama
    this.panorama = panorama;
    
    // set scroller thumb
    this.thumb = thumb;
    }

  
  public void init ()
    {
    // initialize thumb position
    if (thumbSize != null)
      {
      thumbMinPos = thumbSize.width / 2 + 1;
      thumbMaxPos = size ().width - thumbSize.width / 2 - 1;
      thumbPos    = size ().width / 2;
      }
    }
  
  
  public void reset ()
    {
    // reset thumb position
    thumbPos = size ().width / 2;
    
    // repaint scroller
    repaint ();
    }
  
  
  public void set (int position)
    {
    // set new thumb position
    thumbPos = Math.max (thumbMinPos, Math.min (thumbMaxPos, position));
      
    // adjust thumb position
    if (Math.abs (thumbPos - size ().width / 2) <= 3)
      thumbPos = size ().width / 2;
        
    // repaint scroller
    repaint ();
    
    // inform panorama
    if (panorama != null)
      panorama.setAnimationOffset ((thumbPos - size ().width / 2) / 3);
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
    
    // create background if not exist
    if (background == null)
      {
      background = createImage (width, height);
      Graphics backgroundGraphics = background.getGraphics ();
      
      if (((Metamorphose) getParent()).getBackgroundImage () != null) 
        {
        // draw background image
        backgroundGraphics.drawImage (((Metamorphose) getParent()).getBackgroundImage (), 
                                      -location ().x, -location ().y, this);
        
        // wait for background creation
        while ((checkImage (background, this) & ALLBITS) != ALLBITS)
          try 
            {
            Thread.currentThread ().sleep (50);
            }
          catch (InterruptedException exception) {}
        }
      else
        {
        backgroundGraphics.setColor (getParent ().getBackground ());
        backgroundGraphics.fillRect (0, 0, width, height);
        }
      }
    
    // paint background into buffer
    Graphics bufferGraphics = buffer.getGraphics ();
    bufferGraphics.drawImage (background, 0, 0, this);
    
    // paint scroller thumb into buffer
    if (thumb != null)
      {
      // get thumb size and initialize scroller
      if (thumbSize == null)
        {
        thumbSize = new Dimension (thumb.getWidth (this), thumb.getHeight (this));
        init ();
        }
      
      // paint thumb
      bufferGraphics.drawImage (thumb, thumbPos - thumbSize.width / 2, (height - thumbSize.height) / 2, this);
      }
    
    // paint buffer 
    graphics.drawImage (buffer, 0, 0, this);
    }
  
  
  public boolean mouseDrag (Event event, int x, int y)
    {
    // set new thumb position
    set (x);
    
    return true;
    }

   
  public boolean mouseDown (Event event, int x, int y)
    {
    // set new thumb position
    if (Math.abs (thumbPos - x) > 3)
      set (x);
      
    return true;
    }

  }
