<?php
class Calendar {

    private $current_date, $active_year, $active_month, $active_day, $calendar_id;
    public $has_header_nav = true;
    public $booking_id;
    public $has_date_modal = false;

    public function __construct($calendar_id=0, $date = null) 
    {
        $date = !empty($date) ? $date : date('Y-m-d');
        $this->current_date = $date;
        $this->active_year = date('Y', strtotime($date));
        $this->active_month = date('m', strtotime($date));
        $this->active_day = date('d', strtotime($date));
        $this->calendar_id = $calendar_id;
    }

    function set_booking_id($booking_id)
    {
        $this->booking_id = $booking_id;
    }

    function get_months()
    {
        return array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        );
    }

    public function get_years()
    {
        $this_year = date('Y');
        $ahead_years = $this_year+2;
        $prev_years = $this_year-13;
        $years = [];
        for ($i=$this_year; $i <= $ahead_years; $i ++) {
            $years[] = $i;
        }
        for ($i=$this_year; $i >= $prev_years; $i --) {
            $years[] = $i;
        }
        $years = array_unique($years);
        rsort($years);
        return $years;
    }

    public function draw() 
    {
        global $wpcb_booking, $wpcb_setting;
        $num_days = date('t', strtotime($this->current_date));
        $num_days_last_month = date('j', strtotime('last day of previous month', strtotime($this->current_date)));
        $days = $wpcb_booking->get_week_days();
        $first_day_of_week = array_search(date('D', strtotime($this->active_year . '-' . $this->active_month . '-1')), $days);        

        $calendar_header = date('F Y', strtotime($this->current_date));
        $prev_date = date('Y-m-d', strtotime('-1 month',strtotime($this->current_date)));
        $next_date = date('Y-m-d', strtotime('+1 month', strtotime($this->current_date)));
        $enable_days = $wpcb_setting->get_setting('general', 'enable_days');
        $year_month = date('Y-m', strtotime($this->current_date));
        $calendar_data = wpcb_get_calendar_dates($this->calendar_id, $this->booking_id);
        $calendar_data = !empty($calendar_data) && array_key_exists($year_month, $calendar_data) ? $calendar_data[$year_month] : [];
        $calendar_data = apply_filters('wpcb_calendar_data', $calendar_data, $this->calendar_id, $this->booking_id);
        $current_month = date('m');
        ?>
        <?php do_action('wpcb_before_calendar'); ?>
        <input type="hidden" name="year_month" value="<?php echo $year_month; ?>"/>
        <input type="hidden" id="month-name" value="<?php echo date('F'); ?>">
        <input type="hidden" id="calendar_id" name="calendar_id" value="<?php echo $this->calendar_id; ?>"/>
        <input type="hidden" id="booking_id" name="booking_id" value="<?php echo $this->booking_id; ?>"/>
        <input type="hidden" id="go_to_date" data-year="<?php echo $this->active_year ?>" data-month="<?php echo $this->active_month ?>"/>
        <?php if($this->has_date_modal): ?>
            <input type="hidden" id="has_date_modal" name="has_date_modal" value="true"/>
        <?php endif; ?>
        <div class="card">
            <?php if($this->has_header_nav): ?>
            <div class="card-header calendar-header">
                <div class="d-flex justify-content-center align-items-center">
                    <span role="button" class="btn btn-sm update waves-effect px-2 py-1 <?php echo $this->active_month <= $current_month && !is_admin() ? 'disabled': ''?>" data-date="<?php echo $prev_date; ?>"><i class="fa fa-2x fa-angle-left"></i></span>
                    <span id="month-year" class="h3 mx-5 m-0"><?php echo strtoupper($calendar_header); ?></span>
                    <span role="button" class="btn btn-sm update waves-effect px-2 py-1" data-date="<?php echo $next_date; ?>"><i class="fa fa-2x fa-angle-right"></i></span>
                </div>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <div class="days">
                    <!-- Days name -->
                    <?php foreach($days as $day): ?>
                        <div class="day_name"><?php echo $day; ?></div>
                    <?php endforeach; ?>

                    <!-- Past Days -->
                    <?php for($i=$first_day_of_week; $i>0; $i--): ?>
                        <div class="day_num ignore"><?php echo $num_days_last_month-$i+1; ?></div>
                    <?php endfor; ?>

                    <!-- Current month days -->
                    <?php for($i=1; $i<=$num_days; $i++): ?>
                        <?php 
                        $status = '';
                        $current_date = date('Y-m-d', strtotime("{$this->active_year}-{$this->active_month}-{$i}"));
                        $day_name = date('D', strtotime($current_date));
                        $day_class = ($i == $this->active_day) ? 'current' : ''; 
                        if (!empty($calendar_data) && array_key_exists($current_date, $calendar_data) && !wpcb_allow_multiple_booking()) {
                            $status =  $calendar_data[$current_date]['status'];
                        }                        
                        if (empty($status)) {
                            $status = !empty($enable_days) && !in_array($day_name, $enable_days) ? 'disabled' : 'available';
                        }
                        $status = apply_filters('wpcb_calendar_status', $status, $current_date, $calendar_data, $this->booking_id);                      
                        $booked_icon_class =  $status == 'booked' ? 'd-block' : 'd-none';
                        $day_class .= " {$status}";                    
                        ?>
                        <div class="day_num <?php echo $day_class; ?>">
                            <input type="checkbox" name="dates[]" value="<?php echo $i; ?>" class="date-check d-none"/>
                            <?php do_action('wpcb_before_calendar_date', $this->calendar_id, $current_date, $i, $status); ?>
                            <span class="day_num_val"><?php echo $i; ?></span>
                            <span class="booked-status <?php echo $booked_icon_class ?>"><i class="fa fa-check"></i></span>
                            <?php
                                if ($this->has_date_modal) {
                                    wpcb_draw_date_modal($this->calendar_id, $current_date, $i);
                                }
                            ?>
                            <?php do_action('wpcb_after_calendar_date', $this->calendar_id, $current_date, $i, $status); ?>
                        </div>
                    <?php endfor; ?>

                    <!-- Next month days -->
                    <?php for($i=1; $i<=(42-$num_days-max($first_day_of_week, 0)); $i++): ?>
                        <div class="day_num ignore">
                            <?php echo $i; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php do_action('wpcb_after_calendar'); ?>
        <div class="go-to-date">                
            <div class="modal fade text-left" id="calendar-modal" tabindex="-1" role="dialog" aria-labelledby="calendarModal" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="text-center w-100"><?php _e('Go To'); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="option years scale-preset col-12 d-none">
                                <div class="row">
                                    <?php foreach($this->get_years() as $_year): ?>
                                        <?php $active_class = $_year == $this->active_year ? 'active' : ''; ?>
                                        <div class="year item col-3 text-center <?php echo $active_class; ?>" data-value="<?php echo $_year ?>" data-next="months" data-item="year"> <?php echo $_year ?> </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="option months scale-preset col-12 d-none">
                                <div class="row">
                                    <?php foreach($this->get_months() as $_value => $_month): ?>
                                        <?php 
                                        $active = $_value == $this->active_month ? 'active' : ''; 
                                        $disabled = $_value < date('m') ? 'disabled' : '';
                                        ?>
                                        <div class="month item col-3 text-center <?php echo $active.' '.$disabled; ?>" data-value="<?php echo $_value ?>" data-next="done" data-item="month"> <?php echo substr($_month, 0 , 3) ?> </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>