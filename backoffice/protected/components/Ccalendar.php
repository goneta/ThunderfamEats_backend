<?php
class Ccalendar{
    
    public static function daterangepickerdaysOfWeek()
    {
        return [
            t("Su"), 
            t("Mo"), 
            t("Tu"), 
            t("We"), 
            t("Th"), 
            t("Fr"), 
            t("Sa") 
         ]; 
    }

    public static function daterangepickermonthNames()
    {
        return [
            t("January"), 
            t("February"), 
            t("March"), 
            t("April"), 
            t("May"), 
            t("June"), 
            t("July"), 
            t("August"), 
            t("September"), 
            t("October"), 
            t("November"), 
            t("December") 
         ]; 
    }

    public static function elementPLus()
    {
        return [
            "name" => "en",
            "el" => [
                "colorpicker" => [
                    "confirm" => t("OK"),
                    "clear" => t("Clear"),
                    "defaultLabel" => t("color picker"),
                    "description" => t("current color is {color}. press enter to select a new color."),
                ],
                "datepicker" => [
                    "now" => t("Now"),
                    "today" => t("Today"),
                    "cancel" => t("Cancel"),
                    "clear" => t("Clear"),
                    "confirm" => t("OK"),
                    "dateTablePrompt" => t("Use the arrow keys and enter to select the day of the month"),
                    "monthTablePrompt" => t("Use the arrow keys and enter to select the month"),
                    "yearTablePrompt" => t("Use the arrow keys and enter to select the year"),
                    "selectedDate" => t("Selected date"),
                    "selectDate" => t("Select date"),
                    "selectTime" => t("Select time"),
                    "startDate" => t("Start Date"),
                    "startTime" => t("Start Time"),
                    "endDate" => t("End Date"),
                    "endTime" => t("End Time"),
                    "prevYear" => t("Previous Year"),
                    "nextYear" => t("Next Year"),
                    "prevMonth" => t("Previous Month"),
                    "nextMonth" => t("Next Month"),
                    "year" => "",
                    "month1" => t("January"),
                    "month2" => t("February"),
                    "month3" => t("March"),
                    "month4" => t("April"),
                    "month5" => t("May"),
                    "month6" => t("June"),
                    "month7" => t("July"),
                    "month8" => t("August"),
                    "month9" => t("September"),
                    "month10" => t("October"),
                    "month11" => t("November"),
                    "month12" => t("December"),
                    "week" => "week",
                    "weeks" => [
                        "sun" => t("Sun"),
                        "mon" => t("Mon"),
                        "tue" => t("Tue"),
                        "wed" => t("Wed"),
                        "thu" => t("Thu"),
                        "fri" => t("Fri"),
                        "sat" => t("Sat"),
                    ],
                    "weeksFull" => [
                        "sun" => t("Sunday"),
                        "mon" => t("Monday"),
                        "tue" => t("Tuesday"),
                        "wed" => t("Wednesday"),
                        "thu" => t("Thursday"),
                        "fri" => t("Friday"),
                        "sat" => t("Saturday"),
                    ],
                    "months" => [
                        "jan" => t("Jan"),
                        "feb" => t("Feb"),
                        "mar" => t("Mar"),
                        "apr" => t("Apr"),
                        "may" => t("May"),
                        "jun" => t("Jun"),
                        "jul" => t("Jul"),
                        "aug" => t("Aug"),
                        "sep" => t("Sep"),
                        "oct" => t("Oct"),
                        "nov" => t("Nov"),
                        "dec" => t("Dec"),
                    ],
                ],
                "inputNumber" => [
                    "decrease" => t("decrease number"),
                    "increase" => t("increase number"),
                ],
                "select" => [
                    "loading" => t("Loading"),
                    "noMatch" => t("No matching data"),
                    "noData" => t("No data"),
                    "placeholder" => t("Select"),
                ],
                "dropdown" => [
                    "toggleDropdown" => t("Toggle Dropdown"),
                ],
                "cascader" => [
                    "noMatch" => t("No matching data"),
                    "loading" => t("Loading"),
                    "placeholder" => t("Select"),
                    "noData" => t("No data"),
                ],
                "pagination" => [
                    "goto" => t("Go to"),
                    "pagesize" => t("/page"),
                    "total" => t("Total {total}"),
                    "pageClassifier" => "",
                    "page" => t("Page"),
                    "prev" => t("Go to previous page"),
                    "next" => t("Go to next page"),
                    "currentPage" => t("page {pager}"),
                    "prevPages" => t("Previous {pager} pages"),
                    "nextPages" => t("Next {pager} pages"),
                    "deprecationWarning" => t("Deprecated usages detected, please refer to the el-pagination documentation for more details"),
                ],
                "dialog" => [
                    "close" => t("Close this dialog"),
                ],
                "drawer" => [
                    "close" => t("Close this dialog"),
                ],
                "messagebox" => [
                    "title" => t("Message"),
                    "confirm" => t("OK"),
                    "cancel" => t("Cancel"),
                    "error" => t("Illegal input"),
                    "close" => t("Close this dialog"),
                ],
                "upload" => [
                    "deleteTip" => t("press delete to remove"),
                    "delete" => t("Delete"),
                    "preview" => t("Preview"),
                    "continue" => t("Continue"),
                ],
                "slider" => [
                    "defaultLabel" => t("slider between {min} and {max}"),
                    "defaultRangeStartLabel" => t("pick start value"),
                    "defaultRangeEndLabel" => t("pick end value"),
                ],
                "table" => [
                    "emptyText" => t("No Data"),
                    "confirmFilter" => t("Confirm"),
                    "resetFilter" => t("Reset"),
                    "clearFilter" => t("All"),
                    "sumText" => t("Sum"),
                ],
                "tree" => [
                    "emptyText" => t("No Data"),
                ],
                "transfer" => [
                    "noMatch" => t("No matching data"),
                    "noData" => t("No data"),
                    "titles" => [ t("List 1"), t("List 2")],
                    "filterPlaceholder" => t("Enter keyword"),
                    "noCheckedFormat" => t("{total} items"),
                    "hasCheckedFormat" => t("{checked}/{total} checked"),
                ],
                "image" => [
                    "error" => t("FAILED"),
                ],
                "pageHeader" => [
                    "title" => t("Back"),
                ],
                "popconfirm" => [
                    "confirmButtonText" => t("Yes"),
                    "cancelButtonText" => t("No"),
                ],
            ],
        ];        
    }

}
// end class