define([
    'uiComponent',
    'ko',
    'jquery'
], function (Component, ko, $) {
    "use strict";

    return Component.extend({
        defaults: {
            template: 'Customizations_Tasks/course-outline'
        },

        initialize: function () {
            this._super();

            let courseData = this.courseData || { sections: [] };
            let watchedCourses = this.watchedCourse || [];
            console.log('sa', watchedCourses);
            this.selectedCourse = ko.observable('');
            this.selectedImage = ko.observable('');

            if (Object.keys(watchedCourses).length && watchedCourses[this.courseTitle]) {
                let lastRecord = watchedCourses[this.courseTitle].at(-1);
                this.selectedCourse(lastRecord.name);
                this.selectedImage(require.toUrl(lastRecord.image)); 
                console.log('sa1', lastRecord.name);
            }
            else{
                this.selectedCourse(courseData.sections[0].topics[0].name);
                this.selectedImage(require.toUrl(courseData.sections[0].topics[0].image));
                console.log('sa2', courseData.sections[0].topics[0].name);

            }
            // this.courses = ko.observableArray(courseData.sections); original
            //new code
            let selectedCourseName = this.selectedCourse();
            let selectedImageUrl = this.selectedImage();

            let processedSections = courseData.sections.map((section, index) => {
                section.index = index;

                // Only expand the section that has the selected topic and a valid image
                let hasSelectedCourse = section.topics.some(t => t.name === selectedCourseName);
                section.collapsed = ko.observable(!(hasSelectedCourse && selectedImageUrl));

                section.toggleCollapse = function () {
                    this.collapsed(!this.collapsed());
                };

                section.ariaExpanded = ko.pureComputed(() => !section.collapsed());
                section.ariaControlsId = 'courseTopics-' + index;

                return section;
            });

            this.courses = ko.observableArray(processedSections);
            console.log('su3',courseData.sections)
            console.log('su4',processedSections)
        },

        selectCourse: function (name, image){
            this.selectedCourse(name);
            this.selectedImage(require.toUrl(image)); 

            // if (section && section.collapsed && section.collapsed()) {
            //     section.collapsed(false);
            // }

            let isWatched = false;
            if(this.watchedCourse[this.courseTitle]){
                isWatched = this.watchedCourse[this.courseTitle].some(item => item.name === name);
            }
            if(!isWatched){
                $.ajax({
                    url: window.BASE_URL + "customapi/api/markedcourses", 
                    type: 'POST',
                    data: {
                        subtopicTitle: name,
                        courseName: this.courseTitle,
                        image: image
                    },
                    success: function (response) {
                        if (response.success) {
                            console.log("Course marked as watched:", response.data);
                        } else {
                            console.error("Error marking course as watched:", response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", error);
                    }
                });
            }
            return true;
        }
    });
});