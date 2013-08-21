
## USAGE ##

# To use from the command line #

Follow the instructions in utils/README.txt to configure your path. Then type
codecheck from any directory that is within a moodle/totara folder.

# To use as a pre-commit check #

Set up to work from the command line (see above) then create a git alias to an alternative commit command:

cc = !codecheck && git commit

This can be done globally for all repositories with this command:

git config --global alias.cc "!codecheck && git commit"

This would alias 'cc' to check then commit, so use the alias when committing with a check first:

git cc -m "My commit message"

If the code check returns clean the commit will proceed as normal, otherwise it will abort the commit.

To commit without checking, use the normal "git commit" command.

## LOCAL CODE MODIFICATIONS ##

The CodeSniffer came from the Moodle CodeSniffer:
http://docs.moodle.org/dev/CodeSniffer
Commit: 2a3f2b289dfdfe49a5cab25b403b270ca4438249

The following changes have been made:
 - moodle/Sniffs/Files/BoilerplateCommentSniff.php: added Totara boilerplate check

The code from a few functions in locallib.php has been copied into the hook:
 - local_codechecker_clean_path() function (unchanged?)
 - local_codechecker_codesniffer_cli class (unchanged?)
 - local_codesniffer_get_ignores() function (new 1st arg $dirroot?)
 - code from run.php copied into run_codesniffer() function with some changes

