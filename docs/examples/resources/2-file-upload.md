# TwistPHP file and asset upload

Included in the framework is a simple and expandable AJAX file uploader.

**N.B.** Only modern browsers with support for FileReader have the ability to upload asynchronously. Older browsers will revert to using a plain ol' `<input type="file">` element.

## Basic uploader

The basic file uploader can be generated as easily as using a view tag:

```html
<!--
--------------------------------
If you are not using jQuery then
you will need to include it
--------------------------------
-->
{resource:jquery}

<!--
--------------------------------
Output the most simple AJAX file
upload that places files in your
/uploads directory
--------------------------------
-->
{file:upload}

<!--
--------------------------------
...and the simplest asset upload
tool that adds the uploaded file
to your assets table and creates
any supporting thumbnails
--------------------------------
-->
{asset:upload}

<!--
--------------------------------
Change the name attribute of the
input element for posting
--------------------------------
-->
{file:upload,name=myuploadedfile}
{asset:upload,name=myuploadedasset}
```

You also need to register the upload route in your index file:

```php
<?php

    /*
     * --------------------------------
     * The standard path for uploads is
     * set to /upload, but you are free
     * to change this URI by passing in
     * a customised one into the second
     * parameter of the method
     * --------------------------------
     */
    Twist::Route() -> upload();
    //Twist::Route() -> upload( '/yum-yum-files' ); // ...if you really want
```

### Specifying valid file types

By default, the uploader accepts any and all file types. You can change this by using the `accept` parameter.

```html
<!--
--------------------------------
Restrict the input field to only
allow files with mime types that
starts with 'image/' or 'video/'
--------------------------------
-->
{file:upload,accept=image/*,video/*}

<!--
--------------------------------
Accept files whose extension can
be found within this CSV list
--------------------------------
-->
{file:upload,accept=.doc,.docx,.xlsx,.xls,.ppt,.pptx}
```

## Bespoke JavaScript functionality

The standard `{file:upload}` and `{asset:upload}` tags output several different tags at once. You can output each tag and build an uploader yourself.

```html
<!--
--------------------------------
Fileupload JS class that handles
all the requests
--------------------------------
-->
{resource:twist/ajax}

<!--
--------------------------------
Output all the HTML required for
the uploader, when we do it this
way we need to specify an ID for
the uploader JS to reference and
if multiple files are to be done
in a queue the optional multiple
attribute
--------------------------------
-->
{file:upload-html,id=myuploader,multiple=1}

<!--
--------------------------------
Initialise the upload JavaScript
with the uploader ID used above
--------------------------------
-->
{file:upload-init,id=myuploader}
```

Of course, if you are looking to do bespoke things with the upload tool, you'll probably not want to use the standard initialisation script. After you have included jQuery and the JS class, you can call `twistfileupload`.

```js
/*
 * --------------------------------
 * The initialisation of a new file
 * uploader, the first parameter is
 * the ID, the second is the URI to
 * post files to and the third is a
 * settings object
 * --------------------------------
 */
twistfileupload( 'twistupload-myuploader', '/upload/file', { debug: true } );
```

If your registered URI for uploads is `/upload`, file uploads by standard as posted to `/upload/file` whereas assets should be uploaded to `/upload/asset`.

### JavaScript options

You can pass in any of these settings into your `twistfileupload()` call:

| Setting            | Description                                                                                                                    | Type       | Default value                       |
| ------------------ | ------------------------------------------------------------------------------------------ | ---------- | ----------------------------------- |
| abortable          | Defines whether or not you can abort an upload                                                                                 | `boolean`  | `true`                              |
| counter            | Show a counter when uploading multiple files                                                                                   | `boolean`  | `true`                              |
| debug              | Debug all the uploads                                                                                                          | `boolean`  | `false`                             |
| dragdrop           | A jQuery selector to enable a droppable area for the file uploads                                                              | `string`   | `null`                              |
| dropableclass      | Class to add to the droppable area if drag and drop is supported                                                               | `string`   | `'twistupload-dropable'`            |
| hoverclass         | Class to add to the droppable area when an item is dragged over it                                                             | `string`   | `'twistupload-hover'`               |
| invalidtypemessage | Alert to display if an invalid file type is uploaded                                                                           | `string`   | `'This file type is not permitted'` |
| onabort            | Method to be called when the upload is aborted (parameter: file)                                                               | `function` | `function() {}`                     |
| oncompletefile     | Method to be called after each file in a queue is uploaded (parameters: response, file)                                        | `function` | `function() {}`                     |
| oncompletequeue    | Method to be called after every file in a queue is uploaded                                                                    | `function` | `function() {}`                     |
| onerror            | Method to be called upon an upload error (parameter: file)                                                                     | `function` | `function() {}`                     |
| oninvalidtype      | Method to be called upon upload of an invalid type (parameters: file, acceptedtypes, acceptedextensions)                       | `function` | `function() {}`                     |
| onprogress         | Method to be called during the upload of each file (parameters: file, uploaded, totalsize)                                     | `function` | `function() {}`                     |
| onstart            | Method to be called before the file upload queue starts (parameter: file)                                                      | `function` | `function() {}`                     |
| previewsize        | The preview size image to be used (sizes available are set in the TwistPHP settings table)                                     | `integer`  | `128`                               |
| previewsquare      | Use a square preview image                                                                                                     | `boolean`  | `true`                              |

### HTML structure

When used in a modern browser, the code output contains various HTML elements for you to style including elements that are helpful for AJAX uploading.

```html
<div class="twistupload-wrapper">
    <!--
    --------------------------------
    File input field that allows you
    to click and select a file, this
    is the element that is left when
    on older browsers, but will have
    a name parameter - during upload
    requests, this is hidden
    --------------------------------
    -->
    <input class="twistupload" type="file">
    
    <!--
    --------------------------------
    Hidden input containing the true
    returned value (asset id or file
    upload path) which the form will
    post
    --------------------------------
    -->
    <input type="hidden" value="">
    
    <!--
    --------------------------------
    Wrapper for elements that output
    data about the uploads progress,
    only visible when uploads are in
    process
    --------------------------------
    -->
    <div class="twistupload-progress-wrapper">
        <!--
        --------------------------------
        Progress bar showing the current
        upload progress
        --------------------------------
        -->
        <progress class="twistupload-progress" value="0" max="100"></progress>
        
        <!--
        --------------------------------
        Count displaying number of files
        uploaded and number in the queue
        --------------------------------
        -->
        <span class="twistupload-count-wrapper"><span>0</span>/<span>0</span></span>
        
        <!--
        --------------------------------
        Cancel button to abort the files
        remaining in the file queue, and
        also halt the current file
        --------------------------------
        -->
        <button class="twistupload-cancel">Cancel</button>
    </div>
    
    <!--
    --------------------------------
    List of completed uploaded files
    with information about the files
    as well as the ability to remove
    them from the input value posted
    --------------------------------
    -->
    <ul class="twistupload-file-list">
        <li class="twistupload-file-list-item">
        
            <!--
            --------------------------------
            Preview of the uploaded file - a
            non-image file will give an icon
            for that filetype while an image
            will show a small square preview
            --------------------------------
            -->
            <img class="twistupload-file-list-item-preview">
            
            <!--
            --------------------------------
            Details of the uploaded file
            --------------------------------
            -->
            <ul class="twistupload-file-info">
                <li data-key="file/name"><span>File name:</span>myfile.jpg</li>
                <li data-key="file/size"><span>File size:</span>43008</li>
                <li data-key="file/size_pretty"><span>File size (Kb):</span>42</li>
                <li data-key="file_type"><span>File type:</span>Image</li>
            </ul>
            
            <!--
            --------------------------------
            Remove button to delete the file
            from the array of uploaded files
            ready for posting in the form
            --------------------------------
            -->
            <button class="twistupload-file-list-remove">Remove</button>
        </li>
    </ul>
</div>
```