<?php

namespace DL\TicketsGoogleCalendar;

defined('ABSPATH') || exit;

class Plugin
{
    private const DEFAULT_EVENT_DURATION_HOURS = 2;
    private const GOOGLE_CALENDAR_BASE_URL = 'https://www.google.com/calendar/render?action=TEMPLATE';
    private $timezone;

    public function __construct(array $config = [])
    {
        $this->timezone = get_option('timezone_string') ?: 'UTC';
    }

    public function init(): void
    {
        add_filter('dl_ticket_manager_email_ticket_details_after', [$this, 'show_links'], 10, 2);
    }


    public function show_links($ticket)
    {


        $location = [];

        if ($ticket['address']) {
            $location[] = $ticket['address'];
        }

        if ($ticket['city']) {
            $location[] = $ticket['city'];
        }

        if ($ticket['state']) {
            $location[] = $ticket['state'];
        }

        $url = $this->generateGoogleCalendarLink(
            $ticket['event'],
            $ticket['description'],
            implode(', ', $location),
            $this->createDateTime($ticket['date'], $ticket['time']),
            $this->createDateTime($ticket['date'], $ticket['time'], self::DEFAULT_EVENT_DURATION_HOURS)
        );

        echo '<p style="margin: 0;"><a href="' . esc_url($url) . '" target="_blank">' . __('Add to Google Calendar', 'dl-ticket-manager-google-calendar') . '</a></p>';
    }

    /**
     * Método para crear la fecha con la zona horaria correcta
     * @param string $date
     * @param string $time
     * @param int $addHours
     * @return bool|\DateTime
     * @author Daniel Lucia
     */
    private function createDateTime(string $date, string $time, int $addHours = 0): \DateTime
    {
        $timezone = new \DateTimeZone($this->timezone);
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time, $timezone);

        if (!$dateTime) {
            throw new \InvalidArgumentException("Invalid date/time format: {$date} {$time}");
        }

        if ($addHours > 0) {
            $dateTime->modify("+{$addHours} hours");
        }

        return $dateTime;
    }

    /**
     * Genera un enlace a Google Calendar
     * @param string $title
     * @param string $description
     * @param string $location
     * @param \DateTime $start
     * @param \DateTime $end
     * @return string
     * @author Daniel Lucia
     */

    private function generateGoogleCalendarLink(string $title, string $description, string $location, \DateTime $start, \DateTime $end): string
    {
        $baseUrl = "https://www.google.com/calendar/render?action=TEMPLATE";

        $params = [
            "text" => $title,
            "details" => $description,
            "location" => $location,
            "dates" => $start->format('Ymd\THis\Z') . "/" . $end->format('Ymd\THis\Z'),
        ];

        return $baseUrl . "&" . http_build_query($params);
    }
}
