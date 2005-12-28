/*
 * Picture.java
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


// class Picture

public class Picture
  {
  private Image     image  = null;
  private String    name   = null;
  private int       index  = 0;
  private int       count  = 0;
  private Rectangle rect   = null;
  private Vector    frames = null;
  private int       frameCount;
  private int       frameIndex;
  private int       waitingSteps;
  private int       waitingStep;
  
  
  public Picture (Image image, String name, int index, int x, int y, int width, int height, int count, int delay)
    {
    this.image = image;
    this.name  = name;
    this.index = index;
    this.count = count;
    this.rect  = new Rectangle (x, y, width, height);
    
    // initialize waiting steps
    waitingSteps = 1 + Math.max (0, delay);
    waitingStep  = 0;
    }

  
  public Rectangle getRect ()
    {
    return rect;
    }
  
  
  public String getName ()
    {
    return name;
    }
  
  
  public int getIndex ()
    {
    return index;
    }
  
  
  public int getCount ()
    {
    return count;
    }
  
  
  public synchronized void addFrames (Vector frames)
    {
    // create frame list
    this.frames = frames;
    frameCount = frames.size ();
    frameIndex = 0;
    }
  
  
  public synchronized Image getImage ()
    {
    if (frames != null)
      return (Image) frames.elementAt (frameIndex);
    else
      return image;
    }

  
  public synchronized boolean animate ()
    {
    if (frames != null)
      {
      waitingStep = (waitingStep + 1) % waitingSteps;
    
      if (waitingStep == 0)
        {
        frameIndex = (frameIndex + 1) % frameCount;
        return true;
        }
      }
    
    return false;
    }
  
  }
