# Contributing

If you have an idea or bugfix for the library we encourage you to submit your pull request (PR)
or [get in touch with the author](mailto:cspray+labrador@gmail.com).
 
When you do submit your PR we ask that you adhere to the following guideliens:

## Be consistent with your code style

There is no official coding guideline, though one may be established. I have developed my own 
coding style over the years and you can see it displayed in this project. Review some code, I've 
made an effort to keep the coding style consistent, and then copy it with your own PR.

If you have your own habits, IDE templates, or just prefer your own coding style it can be accepted 
assuming it is a new feature and your PR is consistent with its usage of the style. Changes to 
existing code should match the style used for that particular file.
 
This document may be updated later if an official coding guideline is adopted.

## Write unit tests

If it is a bugfix your PR should include, at minimum, a unit test that indicates the failure case and a 
unit test that indicates the bug has been fixed. If it is a new feature ensure that all of the functionality 
has been appropriately covered.

## Update CHANGELOG and docs/ appropriately

The CHANGELOG should have an upcoming minor and bugfix version placeholder. If you are submitting a fix 
add an entry to the bugfix version with the change made. If you are submitting a new feature or removing 
something add an entry to the minor version.

If you added new features or substantially refactored existing functionality please ensure 
the `docs/` directory is up to date.

