<!doctype html>
<html><head lang="en">
    <meta charset="UTF-8">
    <title>awesome bootstrap checkbox demo</title>
    <!-- Bootstrap Core CSS -->
<link href="sb-admin-2/vendor/bootstrap/css/bootstrap.css"
	rel="stylesheet">

<!-- Build Core CSS -->
<link href="sb-admin-2/vendor/bootstrap/css/build.css"
	rel="stylesheet">

<!-- Custom Fonts -->
<link href="sb-admin-2/vendor/font-awesome/css/font-awesome.min.css"
	rel="stylesheet" type="text/css">
   
</head>
<body>
<div class="container">
    <h2>Checkboxes</h2>
    <form role="form">
        <div class="row">
            <div class="col-md-4">
                <fieldset>
                    <legend>
                        Basic
                    </legend>
                    <p>
                        Supports bootstrap brand colors: <code>.checkbox-primary</code>, <code>.checkbox-info</code> etc.
                    </p>
                    <div class="checkbox">
                        <input id="checkbox1" class="styled" type="checkbox">
                        <label for="checkbox1">
                            Default
                        </label>
                    </div>
                    <div class="checkbox checkbox-primary">
                        <input id="checkbox2" class="styled" checked="" type="checkbox">
                        <label for="checkbox2">
                            Primary
                        </label>
                    </div>
                    <div class="checkbox checkbox-success">
                        <input id="checkbox3" class="styled" type="checkbox">
                        <label for="checkbox3">
                            Success
                        </label>
                    </div>
                    <div class="checkbox checkbox-info">
                        <input id="checkbox4" class="styled" type="checkbox">
                        <label for="checkbox4">
                            Info
                        </label>
                    </div>
                    <div class="checkbox checkbox-warning">
                        <input id="checkbox5" class="styled" checked="" type="checkbox">
                        <label for="checkbox5">
                            Warning
                        </label>
                    </div>
                    <div class="checkbox checkbox-danger">
                        <input id="checkbox6" class="styled" checked="" type="checkbox">
                        <label for="checkbox6">
                            Check me out
                        </label>
                    </div>
                    <p>Checkboxes without label text</p>
                    <div class="checkbox">
                        <input class="styled" id="singleCheckbox1" value="option1" aria-label="Single checkbox One" type="checkbox">
                        <label></label>
                    </div>
                    <div class="checkbox checkbox-primary">
                        <input class="styled styled-primary" id="singleCheckbox2" value="option2" checked="" aria-label="Single checkbox Two" type="checkbox">
                        <label></label>
                    </div>
                    <p>Checkboxes with indeterminate state</p>
                    <div class="checkbox checkbox-primary">
                        <input id="indeterminateCheckbox" class="styled" onclick="changeState(this)" type="checkbox">
                        <label></label>
                    </div>
                    <p>Inline checkboxes</p>
                    <div class="checkbox checkbox-inline">
                        <input class="styled" id="inlineCheckbox1" value="option1" type="checkbox">
                        <label for="inlineCheckbox1"> Inline One </label>
                    </div>
                    <div class="checkbox checkbox-success checkbox-inline">
                        <input class="styled" id="inlineCheckbox2" value="option1" checked="" type="checkbox">
                        <label for="inlineCheckbox2"> Inline Two </label>
                    </div>
                    <div class="checkbox checkbox-inline">
                        <input class="styled" id="inlineCheckbox3" value="option1" type="checkbox">
                        <label for="inlineCheckbox3"> Inline Three </label>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-4">
                <fieldset>
                    <legend>
                        Circled
                    </legend>
                    <p>
                        <code>.checkbox-circle</code> for roundness.
                    </p>
                    <div class="checkbox checkbox-circle">
                        <input id="checkbox7" class="styled" type="checkbox">
                        <label for="checkbox7">
                            Simply Rounded
                        </label>
                    </div>
                    <div class="checkbox checkbox-info checkbox-circle">
                        <input id="checkbox8" class="styled" checked="" type="checkbox">
                        <label for="checkbox8">
                            Me too
                        </label>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-4">
                <fieldset>
                    <legend>
                        Disabled
                    </legend>
                    <p>
                        Disabled state also supported.
                    </p>
                    <div class="checkbox">
                        <input class="styled" id="checkbox9" disabled="" type="checkbox">
                        <label for="checkbox9">
                            Can't check this
                        </label>
                    </div>
                    <div class="checkbox checkbox-success">
                        <input class="styled styled" id="checkbox10" disabled="" checked="" type="checkbox">
                        <label for="checkbox10">
                            This too
                        </label>
                    </div>
                    <div class="checkbox checkbox-warning checkbox-circle">
                        <input class="styled" id="checkbox11" disabled="" checked="" type="checkbox">
                        <label for="checkbox11">
                            And this
                        </label>
                    </div>
                </fieldset>
            </div>
        </div>
    </form>
    <h2>Radios</h2>
    <form role="form">
        <div class="row">
            <div class="col-md-4">
                <fieldset>
                    <legend>
                        Basic
                    </legend>
                    <p>
                        Supports bootstrap brand colors: <code>.radio-primary</code>, <code>.radio-danger</code> etc.
                    </p>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="radio">
                                <input name="radio1" id="radio1" value="option1" checked="" type="radio">
                                <label for="radio1">
                                    Small
                                </label>
                            </div>
                            <div class="radio">
                                <input name="radio1" id="radio2" value="option2" type="radio">
                                <label for="radio2">
                                    Big
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="radio radio-danger">
                                <input name="radio2" id="radio3" value="option1" type="radio">
                                <label for="radio3">
                                    Next
                                </label>
                            </div>
                            <div class="radio radio-danger">
                                <input name="radio2" id="radio4" value="option2" checked="" type="radio">
                                <label for="radio4">
                                    One
                                </label>
                            </div>
                        </div>
                    </div>
                    <p>Radios without label text</p>
                    <div class="radio">
                        <input id="singleRadio1" value="option1" name="radioSingle1" aria-label="Single radio One" type="radio">
                        <label></label>
                    </div>
                    <div class="radio radio-success">
                        <input id="singleRadio2" value="option2" name="radioSingle1" checked="" aria-label="Single radio Two" type="radio">
                        <label></label>
                    </div>
                    <p>Inline radios</p>
                    <div class="radio radio-info radio-inline">
                        <input id="inlineRadio1" value="option1" name="radioInline" checked="" type="radio">
                        <label for="inlineRadio1"> Inline One </label>
                    </div>
                    <div class="radio radio-inline">
                        <input id="inlineRadio2" value="option2" name="radioInline" type="radio">
                        <label for="inlineRadio2"> Inline Two </label>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-4">
                <fieldset>
                    <legend>
                        Disabled
                    </legend>
                    <p>
                        Disabled state also supported.
                    </p>
                    <div class="radio radio-danger">
                        <input name="radio3" id="radio5" value="option1" disabled="" type="radio">
                        <label for="radio5">
                            Next
                        </label>
                    </div>
                    <div class="radio">
                        <input name="radio3" id="radio6" value="option2" checked="" disabled="" type="radio">
                        <label for="radio6">
                            One
                        </label>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-4">
                <fieldset>
                    <legend>
                        As Checkboxes
                    </legend>
                    <p>
                        Radios can be made to look like checkboxes.
                    </p>
                    <div class="checkbox checkbox">
                        <input name="radio4" id="radio7" value="option1" checked="" type="radio">
                        <label for="radio7">
                            Default
                        </label>
                    </div>
                    <div class="checkbox checkbox-success">
                        <input name="radio4" id="radio8" value="option2" type="radio">
                        <label for="radio8">
                            Success
                        </label>
                    </div>
                    <div class="checkbox checkbox-danger">
                        <input name="radio4" id="radio9" value="option3" type="radio">
                        <label for="radio9">
                            Danger
                        </label>
                    </div>
                </fieldset>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    function changeState(el) {
        if (el.readOnly) el.checked=el.readOnly=false;
        else if (!el.checked) el.readOnly=el.indeterminate=true;
    }
</script>


</body></html>