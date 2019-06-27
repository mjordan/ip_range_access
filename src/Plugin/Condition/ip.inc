
  /**
   * Loop through IP ranges to see if the client address is within any of them.
   *
   * @param string $ip_ranges
   *   The value of the reaction's configuration form field containing the
   *   IP ranges.
   *
   * @return array
   *   An array that may contain 1 or 0 to indicate whether the client IP
   *   address was within each range.
   */
  private function check_ip($ip_ranges) {
    $is_allowed = array();

    // Get client's IP address and convert it to a long integer for
    // comparison with the registered ranges.
    $comparable_address = ip2long(ip_address());

    $ranges = preg_split("/\\r\\n|\\r|\\n/", $ip_ranges);
    foreach ($ranges as $range) {
      $range = preg_replace('/\s+/', '', $range);
      if (!strlen($range)) {
        continue;
      }
      list($low, $high) = array_pad(explode(':', $range, 2), 2, NULL);

      // Check ranges of IP addresses.
      if (!is_null($low) && !is_null($high)) {
        $comparable_low = ip2long($low);
        $comparable_high = ip2long($high);
        if ($comparable_address >= $comparable_low && $comparable_address <= $comparable_high) {
          $is_allowed[] = 1;
        }
        else {
          $is_allowed[] = 0;
        }
      }

      // Check individual IP addresses.
      if (!is_null($low) && is_null($high)) {
        if (ip_address() == $low) {
          $is_allowed[] = 1;
        }
        else {
          $is_allowed[] = 0;
        }
      }

    }
    return $is_allowed;
  }
