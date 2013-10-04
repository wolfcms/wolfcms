# How to contribute

Third-party patches are essential for keeping Wolf CMS great. We simply can't
access the huge number of platforms and myriad configurations for running
Wolf. We want to keep it as easy as possible to contribute changes that
get things working in your environment. 

There are a few guidelines that we would like external (not core team) contributors
to follow so that we can have a chance of keeping on top of things.

## Getting Started

* Make sure you have a [GitHub account](https://github.com/signup/free)
* Submit a GitHub ticket for your issue, assuming one does not already exist.
  * Clearly describe the issue including steps to reproduce when it is a bug.
  * Make sure you fill in the earliest version that you know has the issue.
* Fork the repository on GitHub

## Making Changes

* Create a topic branch from where you want to base your work.
  * This is usually the `develop` branch.
  * Only target release branches if you are certain your fix must be on that
    branch.
  * To quickly create a topic branch based on `develop`; `git branch
    fix/develop/my_contribution develop` then checkout the new branch with `git
    checkout fix/develop/my_contribution`.  Please avoid working directly on the
    `master` branch, since this is the stable branch.
* Make commits of logical units.
* Check for unnecessary whitespace with `git diff --check` before committing.
* Make sure your commit messages are in the proper format.

````
    (#99999) Make the example in CONTRIBUTING imperative and concrete

    Without this patch applied the example commit message in the CONTRIBUTING
    document is not a concrete example.  This is a problem because the
    contributor is left to imagine what the commit message should look like
    based on a description rather than an example.  This patch fixes the
    problem by making the example concrete and imperative.

    The first line is a real life imperative statement with a ticket number
    from our issue tracker.  The body is optional and describes the behavior
    without the patch, why this is a problem, and how the patch fixes the
    problem when applied.
````

* Make sure you have added the necessary tests for your changes.
* Run _all_ the tests to assure nothing else was accidentally broken.

## Making Trivial Changes

### Documentation

For changes of a trivial nature to comments and documentation, it is not
always necessary to create a new ticket in GitHub. In this case, it is
appropriate to start the first line of a commit with '(doc)' instead of
a ticket number. 

````
    (doc) Add documentation commit example to CONTRIBUTING

    There is no example for contributing a documentation commit
    to Wolf CMS repository. This is a problem because the contributor
    is left to assume how a commit of this nature may appear.

    The first line is a real life imperative statement with '(doc)' in
    place of what would have been the ticket number in a 
    non-documentation related commit. The body describes the nature of
    the new documentation or comments added.
````

## Submitting Changes

* Push your changes to a topic branch in your fork of the repository.
* Submit a pull request to the Wolf CMS repository.
* Update your GitHub issue to mark that you have submitted code and are ready for it to be reviewed.
  * Include a link to the pull request in the ticket

## Important note for core team members

Internally developed features, i.e. by the core team, should be using an `issue-999`
style branch naming scheme within the core's own repository, where `999` refers
to the GitHub issue that describes the feature or bug.

When contributing from their own repository and not working on a branch within the
core's repository, core team members should apply the same procedure as third
party contributors described above.

# Additional Resources

* [Bug tracker (GitHub)](http://www.github.com/wolfcms/wolfcms/issues/)
* [General GitHub documentation](http://help.github.com/)
* [GitHub pull request documentation](http://help.github.com/send-pull-requests/)
