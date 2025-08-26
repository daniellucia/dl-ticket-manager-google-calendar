<?php

class TMGoogleCalendarPlugin
{

    public function init(): void
    {
        add_filter('dl_ticket_manager_email_ticket_details_after', [$this, 'show_links'], 10, 2);
    }


    public function show_links($ticket, $order)
    {

        $location = $ticket['address'] . ' , ' . $ticket['city'] . ' , ' . $ticket['state'];

        $url = $this->generateGoogleCalendarLink(
            $ticket['event'],
            $ticket['description'],
            $location,
            new \DateTime($ticket['date']),
            new \DateTime($ticket['date'])
        );

        echo '<p><a href="' . esc_url($url) . '" target="_blank">' . __('Add to Google Calendar', 'dl-ticket-manager-google-calendar') . '</a></p>';
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
    private function generateGoogleCalendarLink(
        string $title,
        string $description,
        string $location,
        \DateTime $start,
        \DateTime $end
    ): string {
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
