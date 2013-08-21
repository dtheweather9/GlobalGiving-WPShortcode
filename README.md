A plugin for wordpress which connects designated pages with global giving projects and microprojects and reports as to integrate websites.
Example usage: [gg-project projectid="{Project-1 #},{Project-2 #}" giveforyouth="false" reports="true" projectdata="true" images="true" bpgg="true"] {Widget HTML} [/gg-project]
'Projects' are required, and use the Global Giving Project Number.  The first project (comma seperated) will be displayed, and reports and (eventually) images will be displayed from all of the projects. Currently report images from 2nd and more reports are not being brought in. 
'giveforyouth' (default is false) is for use with projects and miniprojects which are listed on giveforyouth.org.  It is not required and will default to false (i.e. the project is on global giving).  
'Reports' (default is true) will display reports for all of the projects requested by the shortcode.  
'projectdata' (default is true) when set to false will hide the general fields, allowing you to have shortcodes for only reports or only images.  'images' (default is true) will display images from projects and reports.  
'bpgg' (default is true) will display the global giving donate sidebox.
