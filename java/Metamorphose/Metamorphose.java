/*
 * Metamorphose.java
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


// class Metamorphose

public class Metamorphose extends Applet implements Runnable
  {
  private Thread   loader     = null;
  private boolean  allLoaded  = false;
  private Image    background = null;
  private Panorama panorama   = null;
  private Scroller scroller   = null;
  
  
  public Metamorphose ()
    {
    // write applet info
    System.out.println (getAppletInfo ());
    }
  
  
  public String getAppletInfo ()
    {
    // return my copyright notice
    return "Metamorphose, Version 1.0"
         + System.getProperty ("line.separator")
         + "Copyright (c) 2000 by Rüdiger Appel, All Rights Reserved" 
         + System.getProperty ("line.separator")
         + "http://www.3quarks.com";
    }

  
  public void init ()
    {
    // set layout manager
    setLayout (null);

    // set background color
    try 
      {
      StringTokenizer tokenizer = new StringTokenizer (getParameter ("BackgroundColor"), ",");
      if (tokenizer.countTokens () == 1)
        {
        String value = tokenizer.nextToken ().trim ();
        setBackground (new Color (Integer.parseInt (value.substring (1, 3), 16),
                                  Integer.parseInt (value.substring (3, 5), 16),
                                  Integer.parseInt (value.substring (5, 7), 16)));
        }
      else
        setBackground (new Color (Integer.parseInt (tokenizer.nextToken ().trim ()),
                                  Integer.parseInt (tokenizer.nextToken ().trim ()), 
                                  Integer.parseInt (tokenizer.nextToken ().trim ())));
      }
    catch (Exception exception) {}
    
    // get panorama geometry and delay
    int canvasPosX     = 0;
    int canvasPosY     = 0;
    int canvasWidth    = 0;
    int canvasHeight   = 0;
    int panoramaWidth  = 0;
    int panoramaHeight = 0;
    int delay          = 50;
    
    try 
      {
      StringTokenizer tokenizer = new StringTokenizer (getParameter ("PanoramaRect"), ",");
      canvasPosX   = Integer.parseInt (tokenizer.nextToken ().trim ());
      canvasPosY   = Integer.parseInt (tokenizer.nextToken ().trim ());
      canvasWidth  = Integer.parseInt (tokenizer.nextToken ().trim ());
      canvasHeight = Integer.parseInt (tokenizer.nextToken ().trim ());
      }
    catch (Exception exception) {}
    
    try 
      {
      StringTokenizer tokenizer = new StringTokenizer (getParameter ("PanoramaSize"), ",");
      panoramaWidth  = Integer.parseInt (tokenizer.nextToken ().trim ());
      panoramaHeight = Integer.parseInt (tokenizer.nextToken ().trim ());
      }
    catch (Exception exception) {}
    
    try 
      {
      delay = Integer.parseInt (getParameter ("Delay").trim ());
      }
    catch (Exception exception) {}
    
    // check panorama geometry and add panorama control
    if ((canvasWidth > 0) && (canvasHeight > 0) && (panoramaHeight > 0) && (panoramaWidth >= canvasWidth))
      {
      // create panorama object
      panorama = new Panorama (panoramaWidth, panoramaHeight, canvasHeight, delay);
    
      // add panorama to layout
      add (panorama);
      
      // set panorama position and size
      panorama.reshape (canvasPosX, canvasPosY, canvasWidth, canvasHeight);
      }
    }

  
  public void update (Graphics graphics)
    {
    // paint applet
    paint (graphics);
    }

  
  public void paint (Graphics graphics)
    {
    // paint background
    if (background != null)
      graphics.drawImage (background, 
                          (size ().width  - background.getWidth  (this)) / 2, 
                          (size ().height - background.getHeight (this)) / 2, 
                          this);
    }


  public Image getBackgroundImage ()
    {
    // return the background image
    return background;
    }
  
  
  public void start ()
    {
    // start loader
    if (!allLoaded)
      {
      loader = new Thread (this);
      loader.start ();
      }
    }


  public void stop ()
    {
    // stop loader
    if (loader != null)
      {
      loader.stop ();
      loader = null;
      }
    
    // stop panorama animation
    if (panorama != null)
      panorama.stop ();
    }
    

  public void run ()
    {
    // loader thread
    if (Thread.currentThread () == loader)
      {
      MediaTracker tracker    = new MediaTracker (this);
      int          identifier = 0;

      // load background image
      showStatus ("loading background...");
      background = loadImage (tracker, getParameter ("BackgroundImage"), identifier++);
      if (background != null)
        repaint ();
      
      // load panorama tile
      showStatus ("loading panorama tile...");
      if (panorama != null)
        panorama.setTile (loadImage (tracker, getParameter ("PanoramaTile"), identifier++));
    
      // get scroller geometry
      int scrollerPosX   = 0;
      int scrollerPosY   = 0;
      int scrollerWidth  = 0;
      int scrollerHeight = 0;
    
      try 
        {
        StringTokenizer tokenizer = new StringTokenizer (getParameter ("ScrollerRect"), ",");
        scrollerPosX   = Integer.parseInt (tokenizer.nextToken ().trim ());
        scrollerPosY   = Integer.parseInt (tokenizer.nextToken ().trim ());
        scrollerWidth  = Integer.parseInt (tokenizer.nextToken ().trim ());
        scrollerHeight = Integer.parseInt (tokenizer.nextToken ().trim ());
        }
      catch (Exception exception) {}

      // load scroller thumb
      Image thumb = loadImage (tracker, getParameter ("ScrollerThumb"), identifier++);
        
      // check scroller geometry and add scroller control
      if ((scrollerWidth > 0) && (scrollerHeight > 0))
        {
        // create scroller object
        scroller = new Scroller (panorama, thumb);
    
        // add scroller to layout
        add (scroller);
      
        // set scroller position and size
        scroller.reshape (scrollerPosX, scrollerPosY, scrollerWidth, scrollerHeight);
        
        // set panorama scroller
        if (panorama != null)
          panorama.setScroller (scroller);
        }
      
      // check panorama
      if (panorama == null)
        {
        // reset browser status line
        showStatus ("done");
        
        return;
        }

      // load panorama images
      String message = getParameter ("PanoramaMessage");
      try
        {
        // parse parameter
        StringTokenizer tokenizer = new StringTokenizer (getParameter ("PanoramaImage"), ",");
        String  name  = tokenizer.nextToken ().trim ();
        int     count = Integer.parseInt (tokenizer.nextToken ().trim ());
  
        // load all panorama images
        for (int index = 1; index <= count; index++)
          {
          // show message in the status line
          showMessage (message, index);
          
          // load the image
          Image image = loadImage (tracker, makeImageName (name, index), identifier++);
          if (image != null)
            panorama.addPanoramaImage (image);
          }
        
        // reset browser status line
        showStatus ("done");
        }
      catch (Exception exception) {}
      
      // get all picture parameter
      Vector pictureParams = new Vector ();
      try
        {
        for (int index = 1; ; index++)
          pictureParams.addElement (getParameter ("Picture" + ((index < 10) ? "0" : "") + index).trim ());
        }
      catch (Exception exception) {}
      
      // create animation list
      Vector animations = new Vector ();

      // load first pictures
      message = getParameter ("PictureMessage");
      for (int index = 0; index < pictureParams.size (); index++)
        {
        try
          {
          // parse parameter
          StringTokenizer tokenizer = new StringTokenizer ((String) pictureParams.elementAt (index), ",");
          String  name  = tokenizer.nextToken ().trim ();
          int     posX  = Integer.parseInt (tokenizer.nextToken ().trim ());
          int     posY  = Integer.parseInt (tokenizer.nextToken ().trim ());
          int     count = 1;
          int     delay = 0;
          
          if (tokenizer.hasMoreTokens ())
            count = Integer.parseInt (tokenizer.nextToken ().trim ());

          if (tokenizer.hasMoreTokens ())
            delay = Integer.parseInt (tokenizer.nextToken ().trim ());

          // show message in the status line
          showMessage (message, index + 1);
          
          // load the image
          Image image = loadImage (tracker, makeImageName (name, 1), identifier++);
          if (image != null)
            {
            Picture picture = panorama.addPicture (image, name, index, posX, posY, count, delay);
            if (count > 1)
              animations.addElement (picture);
            }
          }
        catch (Exception exception) {}
        }
      
      // load animation pictures
      for (int index = 0; index < animations.size (); index++)
        {
        Picture picture = (Picture) animations.elementAt (index);
        String  name    = picture.getName ();
        int     count   = picture.getCount ();
        
        // show message in the status line
        showMessage (message, picture.getIndex () + 1);
          
        // load picture frames
        Vector frames = new Vector ();
        for (int frame = 1; frame <= count; frame++)
          {
          // load the image
          Image image = loadImage (tracker, makeImageName (name, frame), identifier++);
          if (image != null)
            frames.addElement (image);
          }
          
        // add frames to picture
        picture.addFrames (frames);
        }
      
      // reset browser status line
      showStatus ("done");
      
      // set loading flag
      allLoaded = true;
      }
    }

  
  private Image loadImage (MediaTracker tracker, String name, int identifier)
    {
    // check image name
    if (name == null)
      return null;
    
    // load and return an image
    try
      {
      java.net.URLConnection conn = new java.net.URL(getCodeBase(), name.trim()).openConnection();
      //-- Set http referer so G2 hotlink protection won't block request
      conn.setRequestProperty("Referer", getCodeBase().toString());
      Image image = this.createImage((java.awt.image.ImageProducer)conn.getContent());
      tracker.addImage  (image, identifier);
      tracker.waitForID (identifier);
      if (!tracker.isErrorID (identifier))
        return image;
      }
    catch (Exception exception) { exception.printStackTrace(); }

    return null;
    }
  
 
  private String makeImageName (String name, int index)
    {
    int maskIndex = name.indexOf ('#');
    if (maskIndex != -1)
      return name.substring (0, maskIndex)
        + ((index < 10) ? "0" : "") + index
        + name.substring (maskIndex + 1);
        
    return name;
    }
  
  
  private void showMessage (String message, int index)
    {
    if (message != null)
      {
      int textIndex = message.indexOf ('#');
      
      // show message in the status line
      if (textIndex == -1)
        showStatus (message);
      else
        showStatus (message.substring (0, textIndex) + index
                  + message.substring (textIndex + 1));
      }
    }
  
  }
