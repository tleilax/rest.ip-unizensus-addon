rest.ip-unizensus-addon
=======================

Rest.IP: API addition for UniZensus (requires Rest.IP >= 0.9.8)

The course objects of the following routes are extended:

- /courses
- /courses/:course_id
- /courses/semester/:semester_id

The extension follows these rules:

1. If **and only if** unizensus plugin is globally activated and activated for the specific course, the course's **modules** array is extended with another boolean item called **unizensus**.
2. If an unizensus questionaire is currently activate for the specific course, the module item **unizensus** will be **true**.
3. If **and only if** #2 is true, the course object will be extended with another item called **additional_data** (if not already present through another api additional) which will have *at least* the array item **unizensus**. This array itself will have two items:
    * **type** Either *paper* or *online*
    * **url** *False* if `type = paper`, otherwise the url to the online questionaire

Sample response for **/course/:course_id**:
----------------

Note: The array items in the other routes are extended accordingly.

    {
      "course": {
        "course_id": "5e8ff9bf55ba3508199d22e984129be6",
        "start_time": "1301608800",
        "duration_time": "0",
        "number": "1.23.456",
        "title": "Test",
        "subtitle": "",
        "type": "1",
        "modules": {
          "overview": true,
          "admin": true,
          "forum": true,
          "documents": true,
          "schedule": true,
          "participants": true,
          "personal": false,
          "literature": true,
          "wiki": false,
          "scm": true,
          "elearning_interface": false,
          "documents_folder_permissions": false,
          "calendar": false,
          "resources": false,
          "unizensus": true
        },
        "description": null,
        "location": "",
        "semester_id": "1b3810e00cfa7c352848b6e7a9db95a1",
        "teachers": ["8d788385431273d11e8b43bb78f3aa41"],
        "tutors": ["1f6f42334e1709a4e0f9922ad789912b"],
        "students": ["cd73502828457d15655bbd7a63fb0bc8"],
        "color": "#ffffff",
        "additional_data": {
          "unizensus": {
            "type": "online",
            "url": "http://unizensus.example.org/zensus/app?service=pex/StudIpLoginPage&sp=2014-06-23-15-20&sp=d106ce2ef11e9e89d72bc5b74f3bc402&sp=6220e7f4b46446f5426e605389075e9b&sp=questionnaire&sp=f7eb7952b3c494cfb79fa7ed2df9ffac_0bc87ed8b085cccc08e359111a5d53b2&sp=f7eb7952b3c494cfb79fa7ed2df9ffac_0bc87ed8b085cccc08e359111a5d53b2"
          }
        }
      }
    }

    