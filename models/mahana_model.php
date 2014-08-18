<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mahana_model extends CI_Model
{
    /**
     * Send a New Message
     *
     * @param   integer  $sender_id
     * @param   mixed    $recipients  A single integer or an array of integers
     * @param   string   $subject
     * @param   string   $body
     * @param   integer  $priority
     * @return  boolean
     */
    public function send_new_message($sender_id, $recipients, $subject, $body, $priority)
    {
        $this->db->trans_start();

        $thread_id = $this->_insert_thread($subject);
        $msg_id    = $this->_insert_message($thread_id, $sender_id, $body, $priority);

        // Create batch inserts
        $participants[] = array('thread_id' => $thread_id,'user_id' => $sender_id);
        $statuses[]     = array('message_id' => $msg_id, 'user_id' => $sender_id,'status' => $this->config->item('MSG_STATUS_READ'));

        if ( ! is_array($recipients))
        {
            $participants[] = array('thread_id' => $thread_id,'user_id' => $recipients);
            $statuses[]     = array('message_id' => $msg_id, 'user_id' => $recipients, 'status' => $this->config->item('MSG_STATUS_UNREAD'));
        }
        else
        {
            foreach ($recipients as $recipient)
            {
                $participants[] = array('thread_id' => $thread_id,'user_id' => $recipient);
                $statuses[]     = array('message_id' => $msg_id, 'user_id' => $recipient, 'status' => $this->config->item('MSG_STATUS_UNREAD'));
            }
        }

        $this->_insert_participants($participants);
        $this->_insert_statuses($statuses);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        return $thread_id;
    }

    // ------------------------------------------------------------------------

    /**
     * Reply to Message
     *
     * @param   integer  $reply_msg_id
     * @param   integer  $sender_id
     * @param   string   $body
     * @param   integer  $priority
     * @return  boolean
     */
    public function reply_to_message($reply_msg_id, $sender_id, $body, $priority)
    {
        $this->db->trans_start();

        // Get the thread id to keep messages together
        if ( ! $thread_id = $this->_get_thread_id_from_message($reply_msg_id))
        {
            return FALSE;
        }

        // Add this message
        $msg_id = $this->_insert_message($thread_id, $sender_id, $body, $priority);

        if ($recipients = $this->_get_thread_participants($thread_id, $sender_id))
        {
            $statuses[] = array('message_id' => $msg_id, 'user_id' => $sender_id,'status' => $this->config->item('MSG_STATUS_READ'));

            foreach ($recipients as $recipient)
            {
                $statuses[] = array('message_id' => $msg_id, 'user_id' => $recipient['user_id'], 'status' => $this->config->item('MSG_STATUS_UNREAD'));
            }

            $this->_insert_statuses($statuses);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get a Single Message
     *
     * @param  integer $msg_id
     * @param  integer $user_id
     * @return array
     */
    public function get_message($msg_id, $user_id)
    {
        $sql = 'SELECT m.*, m.id as message_id, s.status, t.subject, u.*, p.*' .
        ' FROM ' . $this->db->dbprefix . 'mahana_messages m ' .
        ' JOIN ' . $this->db->dbprefix . 'mahana_threads t ON (m.thread_id = t.id) ' .
        ' JOIN ' . $this->db->dbprefix . 'users u' . ' ON (u.id = m.sender_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'profiles pr' . ' ON (pr.user_id = m.sender_id) ' .        
        ' JOIN ' . $this->db->dbprefix . 'mahana_status s ON (s.message_id = m.id AND s.user_id = ? ) ' .
        ' WHERE m.id = ? ' ;

        $query = $this->db->query($sql, array($user_id, $msg_id));

        return $query->result_array();
    }

    // ------------------------------------------------------------------------

    /**
     * Get a Full Thread
     *
     * @param   integer  $thread_id
     * @param   integer  $user_id
     * @param   boolean  $full_thread
     * @param   string   $order_by
     * @return  array
     */
    public function get_full_thread($thread_id, $user_id, $full_thread = FALSE, $order_by = 'asc')
    {
        $sql = 'SELECT m.*, m.id as message_id, s.status, t.subject, u.*, p.*, pr.*' .
        ' FROM ' . $this->db->dbprefix . 'mahana_participants p ' .
        ' JOIN ' . $this->db->dbprefix . 'mahana_threads t ON (t.id = p.thread_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'mahana_messages m ON (m.thread_id = t.id) ' .
        ' JOIN ' . $this->db->dbprefix . 'users u' . ' ON (u.id = m.sender_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'profiles pr' . ' ON (pr.user_id = m.sender_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'mahana_status s ON (s.message_id = m.id AND s.user_id = ? ) ' .
        ' WHERE p.user_id = ? ' .
        ' AND p.thread_id = ? ';

        if ( ! $full_thread)
        {
            $sql .= ' AND m.cdate >= p.cdate';
        }

        $sql .= ' ORDER BY m.cdate ' . $order_by;

        $query = $this->db->query($sql, array($user_id, $user_id, $thread_id));

        return $query->result_array();
    }

    // ------------------------------------------------------------------------

    /**
     * Get All Threads
     *
     * @param   integer  $user_id
     * @param   boolean  $full_thread
     * @param   string   $order_by
     * @return  array
     */
    public function get_all_threads($user_id, $full_thread = FALSE, $order_by = 'asc')
    {
        $sql = 'SELECT m.*, m.id as message_id, s.status, t.subject, u.*, pr.*' .
        ' FROM ' . $this->db->dbprefix . 'mahana_participants p ' .
        ' JOIN ' . $this->db->dbprefix . 'mahana_threads t ON (t.id = p.thread_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'mahana_messages m ON (m.thread_id = t.id) ' .
        ' JOIN ' . $this->db->dbprefix . 'users u' . ' ON (u.id = m.sender_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'profiles pr' . ' ON (pr.user_id = u.id) ' .
        ' JOIN ' . $this->db->dbprefix . 'mahana_status s ON (s.message_id = m.id AND s.user_id = ? ) ' .
        ' WHERE p.user_id = ? ' ;

        if (!$full_thread)
        {
            $sql .= ' AND m.cdate >= p.cdate';
        }

        $sql .= ' ORDER BY t.id ' . $order_by. ', m.cdate '. $order_by;

        $query = $this->db->query($sql, array($user_id, $user_id));

        return $query->result_array();
    }
    
    /**
     * Get All Threads From All Users
     *
     * @param   boolean  $full_thread
     * @param   string   $order_by
     * @return  array
     */
    public function get_all_threads_from_all($order_by = 'asc')
    {
        $sql = 'SELECT m.*, u.*, pr.*, t.*' .
        ' FROM ' . $this->db->dbprefix . 'mahana_messages m ' .
        ' JOIN ' . $this->db->dbprefix . 'mahana_threads t ON (t.id = m.thread_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'users u' . ' ON (u.id = m.sender_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'profiles pr' . ' ON (pr.user_id = u.id) ';

        $sql .= ' ORDER BY t.id ' . $order_by. ', m.cdate '. $order_by;

        $query = $this->db->query($sql);

        return $query->result_array();
    }

    // ------------------------------------------------------------------------

    /**
     * Change Message Status
     *
     * @param   integer  $msg_id
     * @param   integer  $user_id
     * @param   integer  $status_id
     * @return  integer
     */
    public function update_message_status($msg_id, $user_id, $status_id)
    {
        $this->db->where(array('message_id' => $msg_id, 'user_id' => $user_id ));
        $this->db->update('mahana_status', array('status' => $status_id ));

        return $this->db->affected_rows();
    }

    // ------------------------------------------------------------------------

    /**
     * Add a Participant
     *
     * @param   integer  $thread_id
     * @param   integer  $user_id
     * @return  boolean
     */
    public function add_participant($thread_id, $user_id)
    {
        $this->db->trans_start();

        $participants[] = array('thread_id' => $thread_id,'user_id' => $user_id);

        $this->_insert_participants($participants);

        // Get Messages by Thread
        $messages = $this->_get_messages_by_thread_id($thread_id);

        foreach ($messages as $message)
        {
            $statuses[] = array('message_id' => $message['id'], 'user_id' => $user_id, 'status' => $this->config->item('MSG_STATUS_UNREAD'));
        }

        $this->_insert_statuses($statuses);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Remove a Participant
     *
     * @param   integer  $thread_id
     * @param   integer  $user_id
     * @return  boolean
     */
    public function remove_participant($thread_id, $user_id)
    {
        $this->db->trans_start();

        $this->_delete_participant($thread_id, $user_id);
        $this->_delete_statuses($thread_id, $user_id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Valid New Participant - because of CodeIgniter's DB Class return style,
     *                         it is safer to check for uniqueness first
     *
     * @param   integer $thread_id
     * @param   integer $user_id
     * @return  boolean
     */
    public function valid_new_participant($thread_id, $user_id)
    {
        $sql = 'SELECT COUNT(*) AS count ' .
        ' FROM ' . $this->db->dbprefix . 'mahana_participants p ' .
        ' WHERE p.thread_id = ? ' .
        ' AND p.user_id = ? ';

        $query = $this->db->query($sql, array($thread_id, $user_id));

        if ($query->row()->count)
        {
            return FALSE;
        }

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Application User
     *
     * @param   integer  $user_id`
     * @return  boolean
     */
    public function application_user($user_id)
    {
        $sql = 'SELECT COUNT(*) AS count ' .
        ' FROM ' . $this->db->dbprefix . 'users' .
        ' WHERE users.id = ?' ;

        $query = $this->db->query($sql, array($user_id));

        if ($query->row()->count)
        {
            return TRUE;
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Participant List
     *
     * @param   integer  $thread_id
     * @param   integer  $sender_id
     * @return  mixed
     */
    public function get_participant_list($thread_id, $sender_id = 0)
    {
        if ($results = $this->_get_thread_participants($thread_id, $sender_id))
        {
            return $results;
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Message Count
     *
     * @param   integer  $user_id
     * @param   integer  $status_id
     * @return  integer
     */
    public function get_msg_count($user_id, $status_id = false)
    {
    
    	$status_id = $status_id ? $status_id : $this->config->item('MSG_STATUS_UNREAD');
    	
        $query = $this->db->select('COUNT(*) AS msg_count')->where(array('user_id' => $user_id, 'status' => $status_id ))->get('mahana_status');

        return $query->row()->msg_count;
    }

    // ------------------------------------------------------------------------
    // Private Functions from here out!
    // ------------------------------------------------------------------------

    /**
     * Insert Thread
     *
     * @param   string  $subject
     * @return  integer
     */
    private function _insert_thread($subject)
    {
        $insert_id = $this->db->insert('mahana_threads', array('subject' => $subject));

        return $this->db->insert_id();
    }

    /**
     * Insert Message
     *
     * @param   integer  $thread_id
     * @param   integer  $sender_id
     * @param   string   $body
     * @param   integer  $priority
     * @return  integer
     */
    private function _insert_message($thread_id, $sender_id, $body, $priority)
    {
        $insert['thread_id'] = $thread_id;
        $insert['sender_id'] = $sender_id;
        $insert['body']      = $body;
        $insert['priority']  = $priority;

        $insert_id = $this->db->insert('mahana_messages', $insert);

        return $this->db->insert_id();
    }

    /**
     * Insert Participants
     *
     * @param   array  $participants
     * @return  bool
     */
    private function _insert_participants($participants)
    {
        return $this->db->insert_batch('mahana_participants', $participants);
    }

    /**
     * Insert Statuses
     *
     * @param   array  $statuses
     * @return  bool
     */
    private function _insert_statuses($statuses)
    {
        return $this->db->insert_batch('mahana_status', $statuses);
    }

    /**
     * Get Thread ID from Message
     *
     * @param   integer  $msg_id
     * @return  integer
     */
    private function _get_thread_id_from_message($msg_id)
    {
        $query = $this->db->select('thread_id')->get_where('mahana_messages', array('id' => $msg_id));

        if ($query->num_rows())
        {
            return $query->row()->thread_id;
        }
        return 0;
    }

    /**
     * Get Messages by Thread
     *
     * @param   integer  $thread_id
     * @return  array
     */
    private function _get_messages_by_thread_id($thread_id)
    {
        $query = $this->db->get_where('mahana_messages', array('thread_id' => $thread_id));

        return $query->result_array();
    }


    /**
     * Get Thread Particpiants
     *
     * @param   integer  $thread_id
     * @param   integer  $sender_id
     * @return  array
     */
    private function _get_thread_participants($thread_id, $sender_id = 0)
    {
        $array['thread_id'] = $thread_id;

        if ($sender_id) // If $sender_id 0, no one to exclude
        {
            $array['mahana_participants.user_id != '] = $sender_id;
        }

        $this->db->select('mahana_participants.user_id, users.*, profiles.*', FALSE);
        $this->db->join('users', 'mahana_participants.user_id = users.id');
        $this->db->join('profiles', 'mahana_participants.user_id = profiles.user_id');        

        $query = $this->db->get_where('mahana_participants', $array);

        return $query->result_array();
    }

    /**
     * Delete Participant
     *
     * @param   integer  $thread_id
     * @param   integer  $user_id
     * @return  boolean
     */
    private function _delete_participant($thread_id, $user_id)
    {
        $this->db->delete('mahana_participants', array('thread_id' => $thread_id, 'user_id' => $user_id));

        if ($this->db->affected_rows() > 0)
        {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Delete Statuses
     *
     * @param   integer  $thread_id
     * @param   integer  $user_id
     * @return  boolean
     */
    private function _delete_statuses($thread_id, $user_id)
    {
        $sql = 'DELETE s FROM msg_status s ' .
        ' JOIN ' . $this->db->dbprefix . 'mahana_messages m ON (m.id = s.message_id) ' .
        ' WHERE m.thread_id = ? ' .
        ' AND s.user_id = ? ';

        $query = $this->db->query($sql, array($thread_id, $user_id));

        return TRUE;
    }
}

/* end of file mahana_model.php */