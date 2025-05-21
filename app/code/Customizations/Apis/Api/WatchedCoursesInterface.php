<?php
namespace Customizations\Apis\Api;

interface WatchedCoursesInterface
{
    /**
     * Mark a course as watched.
     *
     * @param string $subtopicTitle
     * @param string $courseName
     * @param string $image
     * @param int|null $customerId (Optional)
     * @return string
     */
    public function markCourseAsWatched($subtopicTitle, $courseName, $image, $customerId = null);

    /**
     * Get all watched courses.
     *
     * @param int|null $customerId (Optional)
     * @return array
     */
    public function getWatchedCourses($customerId = null);
}
