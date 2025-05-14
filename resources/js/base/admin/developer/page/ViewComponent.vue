<template>
  <div class="block">
    <el-form
        v-loading="loading"
        element-loading-text="Loading..."
        element-loading-spinner="el-icon-loading"
        element-loading-background="rgba(0, 0, 0, 0.8)"
        ref="form"
        :model="form"
        class="form-horizontal form-bordered"
    >
      <el-row>

        <!-- Developer Name -->
        <div class="form-group">
          <label class="col-md-2 control-label">Developer Name:</label>
          <div class="col-md-6">{{ form.name }}</div>
        </div>

        <!-- ID Code -->
        <div class="form-group">
          <label class="col-md-2 control-label">ID Code:</label>
          <div class="col-md-6">{{ form.id_code }}</div>
        </div>

        <!-- Representative -->
        <div class="form-group">
          <label class="col-md-2 control-label">Representative:</label>
          <div class="col-md-6">{{ form.representative }}</div>
        </div>

        <!-- Tel -->
        <div class="form-group">
          <label class="col-md-2 control-label">Tel:</label>
          <div class="col-md-6">{{ form.tel }}</div>
        </div>

        <!-- Representative Position -->
        <div class="form-group">
          <label class="col-md-2 control-label">Representative Position:</label>
          <div class="col-md-6">{{ form.representative_position }}</div>
        </div>

        <!-- Service Agreement -->
        <div class="form-group dashed" v-if="form.service_agreement">
          <label class="col-md-2 control-label">Service Agreement:</label>
          <div class="col-md-6">
            <a :href="form.service_agreement" target="_blank">View Agreement</a>
          </div>
        </div>

        <!-- Logo -->
        <div class="form-group dashed" v-if="form.logo">
          <label class="col-md-2 control-label">Logo:</label>
          <div class="col-md-6">
            <ImageModal
                :thumbnail="form.logo"
                :image-path="form.logo"
                :rounded="false"
                :width="200"
            />
          </div>
        </div>

        <!-- Stamp -->
        <div class="form-group dashed" v-if="form.stamp">
          <label class="col-md-2 control-label">Stamp:</label>
          <div class="col-md-6">
            <ImageModal
                :thumbnail="form.stamp"
                :image-path="form.stamp"
                :rounded="false"
                :width="200"
            />
          </div>
        </div>

        <!-- Signature -->
        <div class="form-group dashed" v-if="form.signature">
          <label class="col-md-2 control-label">Signature:</label>
          <div class="col-md-6">
            <ImageModal
                :thumbnail="form.signature"
                :image-path="form.signature"
                :rounded="false"
                :width="200"
            />
          </div>
        </div>

        <!-- Username -->
        <div class="form-group">
          <label class="col-md-2 control-label">Username:</label>
          <div class="col-md-6">{{ form.username }}</div>
        </div>

      </el-row>
    </el-form>
  </div>
</template>

<script>
import { responseParse } from '../../../mixins/responseParse'
import { getData } from '../../../mixins/getData'
import ImageModal from '../../../components/admin/ImageModal.vue'

export default {
  props: ['getSaveDataRoute', 'id'],
  data() {
    return {
      loading: false,
      routes: {},
      form: {
        id: null,
        developer_name: '',
        id_code: '',
        representative: '',
        tel: '',
        representative_position: '',
        service_agreement_url: '',
        logo_url: '',
        stamp_url: '',
        signature_url: '',
        username: '',
        password: ''
      }
    }
  },
  created() {
    if (this.id) this.form.id = this.id
    this.getSaveData()
  },
  methods: {
    async getSaveData() {
      this.loading = true
      const res = await getData({
        method: 'POST',
        url: this.getSaveDataRoute,
        data: this.form
      })
      responseParse(res, false)
      if (res.status === 200 && res.data.data.item) {
        const item = res.data.data.item
        this.form = {
          ...this.form,
          ...item,
          service_agreement_url: item.service_agreement_url || '',
          logo_url: item.logo_url || '',
          stamp_url: item.stamp_url || '',
          signature_url: item.signature_url || ''
        }
        this.routes = res.data.data.routes
      }
      this.loading = false
    }
  },
  components: { ImageModal }
}
</script>

<style>
/* add any custom styles here */
</style>
