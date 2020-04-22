<?php
class ControllerExtensionPaymentPPCheckout extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/payment/pp_checkout');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_pp_checkout', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['email'])) {
            $data['error_email'] = $this->error['email'];
        } else {
            $data['error_email'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/pp_checkout', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/pp_checkout', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        //client_id
        if (isset($this->request->post['payment_pp_checkout_client_id'])) {
            $data['payment_pp_checkout_client_id'] = $this->request->post['payment_pp_checkout_client_id'];
        } else {
            $data['payment_pp_checkout_client_id'] = $this->config->get('payment_pp_checkout_client_id');
        }

        //client_secret
        if(isset($this->request->post['payment_pp_checkout_client_secret'])) {
            $data['payment_pp_checkout_client_secret'] = $this->request->post['payment_pp_checkout_client_secret'];
        } else {
            $data['payment_pp_checkout_client_secret'] = $this->config->get('payment_pp_checkout_client_secret');
        }

        //test
        if (isset($this->request->post['payment_pp_checkout_test'])) {
            $data['payment_pp_checkout_test'] = $this->request->post['payment_pp_checkout_test'];
        } else {
            $data['payment_pp_checkout_test'] = $this->config->get('payment_pp_checkout_test');
        }

        //交易方式
        if (isset($this->request->post['payment_pp_checkout_transaction'])) {
            $data['payment_pp_checkout_transaction'] = $this->request->post['payment_pp_checkout_transaction'];
        } else {
            $data['payment_pp_checkout_transaction'] = $this->config->get('payment_pp_checkout_transaction');
        }

        //开启调试模式
        if (isset($this->request->post['payment_pp_checkout_debug'])) {
            $data['payment_pp_checkout_debug'] = $this->request->post['payment_pp_checkout_debug'];
        } else {
            $data['payment_pp_checkout_debug'] = $this->config->get('payment_pp_checkout_debug');
        }

        //支持最低金额
        if (isset($this->request->post['payment_pp_checkout_total'])) {
            $data['payment_pp_checkout_total'] = $this->request->post['payment_pp_checkout_total'];
        } else {
            $data['payment_pp_checkout_total'] = $this->config->get('payment_pp_checkout_total');
        }

        //取消状态
        if (isset($this->request->post['payment_pp_checkout_canceled_reversal_status_id'])) {
            $data['payment_pp_checkout_canceled_reversal_status_id'] = $this->request->post['payment_pp_checkout_canceled_reversal_status_id'];
        } else {
            $data['payment_pp_checkout_canceled_reversal_status_id'] = $this->config->get('payment_pp_checkout_canceled_reversal_status_id');
        }
        //完成状态
        if (isset($this->request->post['payment_pp_checkout_completed_status_id'])) {
            $data['payment_pp_checkout_completed_status_id'] = $this->request->post['payment_pp_checkout_completed_status_id'];
        } else {
            $data['payment_pp_checkout_completed_status_id'] = $this->config->get('payment_pp_checkout_completed_status_id');
        }
        //拒绝状态
        if (isset($this->request->post['payment_pp_checkout_denied_status_id'])) {
            $data['payment_pp_checkout_denied_status_id'] = $this->request->post['payment_pp_checkout_denied_status_id'];
        } else {
            $data['payment_pp_checkout_denied_status_id'] = $this->config->get('payment_pp_checkout_denied_status_id');
        }
        //过期状态
        if (isset($this->request->post['payment_pp_checkout_expired_status_id'])) {
            $data['payment_pp_checkout_expired_status_id'] = $this->request->post['payment_pp_checkout_expired_status_id'];
        } else {
            $data['payment_pp_checkout_expired_status_id'] = $this->config->get('payment_pp_checkout_expired_status_id');
        }
        //失败状态
        if (isset($this->request->post['payment_pp_checkout_failed_status_id'])) {
            $data['payment_pp_checkout_failed_status_id'] = $this->request->post['payment_pp_checkout_failed_status_id'];
        } else {
            $data['payment_pp_checkout_failed_status_id'] = $this->config->get('payment_pp_checkout_failed_status_id');
        }
        //代处理状态
        if (isset($this->request->post['payment_pp_checkout_pending_status_id'])) {
            $data['payment_pp_checkout_pending_status_id'] = $this->request->post['payment_pp_checkout_pending_status_id'];
        } else {
            $data['payment_pp_checkout_pending_status_id'] = $this->config->get('payment_pp_checkout_pending_status_id');
        }
        //处理状态
        if (isset($this->request->post['payment_pp_checkout_processed_status_id'])) {
            $data['payment_pp_checkout_processed_status_id'] = $this->request->post['payment_pp_checkout_processed_status_id'];
        } else {
            $data['payment_pp_checkout_processed_status_id'] = $this->config->get('payment_pp_checkout_processed_status_id');
        }
        //退货状态
        if (isset($this->request->post['payment_pp_checkout_refunded_status_id'])) {
            $data['payment_pp_checkout_refunded_status_id'] = $this->request->post['payment_pp_checkout_refunded_status_id'];
        } else {
            $data['payment_pp_checkout_refunded_status_id'] = $this->config->get('payment_pp_checkout_refunded_status_id');
        }
        //拒收状态
        if (isset($this->request->post['payment_pp_checkout_reversed_status_id'])) {
            $data['payment_pp_checkout_reversed_status_id'] = $this->request->post['payment_pp_checkout_reversed_status_id'];
        } else {
            $data['payment_pp_checkout_reversed_status_id'] = $this->config->get('payment_pp_checkout_reversed_status_id');
        }
        //无效状态
        if (isset($this->request->post['payment_pp_checkout_voided_status_id'])) {
            $data['payment_pp_checkout_voided_status_id'] = $this->request->post['payment_pp_checkout_voided_status_id'];
        } else {
            $data['payment_pp_checkout_voided_status_id'] = $this->config->get('payment_pp_checkout_voided_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        //区域
        if (isset($this->request->post['payment_pp_checkout_geo_zone_id'])) {
            $data['payment_pp_checkout_geo_zone_id'] = $this->request->post['payment_pp_checkout_geo_zone_id'];
        } else {
            $data['payment_pp_checkout_geo_zone_id'] = $this->config->get('payment_pp_checkout_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        //禁用，启用
        if (isset($this->request->post['payment_pp_checkout_status'])) {
            $data['payment_pp_checkout_status'] = $this->request->post['payment_pp_checkout_status'];
        } else {
            $data['payment_pp_checkout_status'] = $this->config->get('payment_pp_checkout_status');
        }

        //排序
        if (isset($this->request->post['payment_pp_checkout_sort_order'])) {
            $data['payment_pp_checkout_sort_order'] = $this->request->post['payment_pp_checkout_sort_order'];
        } else {
            $data['payment_pp_checkout_sort_order'] = $this->config->get('payment_pp_checkout_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/pp_checkout', $data));
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/pp_checkout')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_pp_checkout_client_id']) {
            $this->error['error_client_id'] = $this->language->get('error_client_id');
        }

        if (!$this->request->post['payment_pp_checkout_client_secret']) {
            $this->error['error_client_secret'] = $this->language->get('error_client_secret');
        }

        return !$this->error;
    }
}