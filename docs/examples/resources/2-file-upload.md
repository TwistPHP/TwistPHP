# TwistPHP file and asset upload

Included in the framework is a simple and expandable AJAX file uploader.

**N.B.** Only modern browsers with support for FileReader have the ability to upload asynchronously. Older browsers will revert to using a plain ol' `<input type="file">` element.

## Basic uploader

The basic

```html
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
```

When used in a modern browser, the code output contains various HTML elements for you to style.

```html
<!--
--------------------------------
The main file upload wrapper
--------------------------------
-->
<div class="twistupload-wrapper">
    <!--
    --------------------------------
    File input field that allows you
    to click and select a file, this
    is the element that is left when
    on older browsers, but will have
    a name parameter
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
    <ul class="twistupload-file-list"></ul>
</div>
```

### Multiple files